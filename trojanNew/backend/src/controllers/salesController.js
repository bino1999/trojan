const supabase = require('../config/supabase')
const stockService = require('../services/stockService')

exports.list = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('sales')
      .select('*, customers(name)')
      .order('sale_date', { ascending: false })
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.get = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('sales')
      .select('*, customers(*), sale_items(*, products(*), inventory(selling_price))')
      .eq('sale_id', req.params.id)
      .single()
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.create = async (req, res, next) => {
  try {
    const { customer_id, sale_date, payment_method, notes, items } = req.body

    const total_amount = items.reduce((sum, i) => sum + i.line_total, 0)

    const { data: sale, error: saleErr } = await supabase
      .from('sales')
      .insert({ customer_id, sale_date, payment_method, notes, total_amount, created_by: req.user.id })
      .select().single()
    if (saleErr) throw saleErr

    const lineItems = items.map((i) => ({
      sale_id: sale.sale_id,
      inventory_id: i.inventory_id,
      product_id: i.product_id,
      qty_sold: i.qty_sold,
      unit_price: i.unit_price,
      line_total: i.line_total,
    }))
    const { error: itemErr } = await supabase.from('sale_items').insert(lineItems)
    if (itemErr) throw itemErr

    await Promise.all(items.map((i) => stockService.decrementStock(i.inventory_id, i.qty_sold)))

    res.status(201).json(sale)
  } catch (err) { next(err) }
}
