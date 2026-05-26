const supabase = require('../config/supabase')
const stockService = require('../services/stockService')

exports.list = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('service_jobs')
      .select('*, customers(name), vehicles(plate_number, make, model)')
      .order('job_date', { ascending: false })
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.get = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('service_jobs')
      .select('*, customers(*), vehicles(*), service_job_items(*, products(*), inventory(selling_price))')
      .eq('job_id', req.params.id)
      .single()
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.create = async (req, res, next) => {
  try {
    const { customer_id, vehicle_id, assigned_technician, job_date, labor_description, labor_cost, notes } = req.body
    const { data, error } = await supabase
      .from('service_jobs')
      .insert({ customer_id, vehicle_id, assigned_technician, job_date, labor_description, labor_cost, notes, status: 'open' })
      .select().single()
    if (error) throw error
    res.status(201).json(data)
  } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
  try {
    const { labor_description, labor_cost, notes, assigned_technician } = req.body
    const { data, error } = await supabase
      .from('service_jobs')
      .update({ labor_description, labor_cost, notes, assigned_technician })
      .eq('job_id', req.params.id)
      .select().single()
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.addItem = async (req, res, next) => {
  try {
    const { inventory_id, product_id, qty_used, unit_price } = req.body
    await stockService.decrementStock(inventory_id, qty_used)

    const { data, error } = await supabase
      .from('service_job_items')
      .insert({ job_id: req.params.id, inventory_id, product_id, qty_used, unit_price, line_total: qty_used * unit_price })
      .select().single()
    if (error) throw error
    res.status(201).json(data)
  } catch (err) { next(err) }
}

exports.removeItem = async (req, res, next) => {
  try {
    const { data: item } = await supabase
      .from('service_job_items').select('inventory_id, qty_used').eq('job_item_id', req.params.itemId).single()

    await stockService.incrementStock(item.inventory_id, item.qty_used)

    const { error } = await supabase.from('service_job_items').delete().eq('job_item_id', req.params.itemId)
    if (error) throw error
    res.status(204).end()
  } catch (err) { next(err) }
}

exports.complete = async (req, res, next) => {
  try {
    const { error } = await supabase
      .from('service_jobs').update({ status: 'completed' }).eq('job_id', req.params.id)
    if (error) throw error
    res.json({ success: true })
  } catch (err) { next(err) }
}
