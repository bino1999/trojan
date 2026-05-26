const supabase = require('../config/supabase')

exports.stock = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('inventory')
      .select('*, products(name, category, unit), suppliers(name)')
      .order('qty_in_stock')
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.stockMovement = async (req, res, next) => {
  try {
    const { product_id, from, to } = req.query
    // TODO: aggregate across purchase_order_items, internal_use_records, sale_items, service_job_items, return_items, stock_adjustments
    res.json({ message: 'TODO: implement stock movement aggregation', product_id, from, to })
  } catch (err) { next(err) }
}

exports.supplierPurchases = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('purchase_orders')
      .select('*, suppliers(name), purchase_order_items(*)')
      .eq('status', 'received')
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.sales = async (req, res, next) => {
  try {
    const { from, to } = req.query
    let q = supabase.from('sales').select('*, sale_items(*)')
    if (from) q = q.gte('sale_date', from)
    if (to) q = q.lte('sale_date', to)
    const { data, error } = await q
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.serviceJobs = async (req, res, next) => {
  try {
    const { from, to } = req.query
    let q = supabase.from('service_jobs').select('*, service_job_items(*)')
    if (from) q = q.gte('job_date', from)
    if (to) q = q.lte('job_date', to)
    const { data, error } = await q
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.internalUse = async (req, res, next) => {
  try {
    const { from, to } = req.query
    let q = supabase.from('internal_use_records').select('*, products(name)')
    if (from) q = q.gte('date_used', from)
    if (to) q = q.lte('date_used', to)
    const { data, error } = await q
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.returns = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('returns').select('*, return_items(*, products(name))')
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.profitMargin = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('inventory')
      .select('company_price, selling_price, products(name, category)')
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.lowStock = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('inventory')
      .select('*, products(name, category, unit)')
      .filter('qty_in_stock', 'lte', 'reorder_level')
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}
