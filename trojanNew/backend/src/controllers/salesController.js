const supabase = require('../config/supabase')
const stockService = require('../services/stockService')

function addId(data) {
  if (!data) return data
  if (Array.isArray(data)) return data.map(r => ({ ...r, id: r.sale_id }))
  return { ...data, id: data.sale_id }
}

exports.list = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('sales')
      .select('*, customers(name)')
      .order('sale_date', { ascending: false })
    if (error) throw error
    res.json(addId(data))
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
    const result = {
      ...data,
      id: data.sale_id,
      sale_items: (data.sale_items ?? []).map(i => ({ ...i, id: i.sale_item_id })),
    }
    res.json(result)
  } catch (err) { next(err) }
}

exports.create = async (req, res, next) => {
  try {
    const { customer_id, sale_date, payment_method, notes, items } = req.body

    const total_amount = items.reduce((sum, i) => sum + (i.qty_sold * i.unit_price), 0)

    const { data: sale, error: saleErr } = await supabase
      .from('sales')
      .insert({ customer_id: customer_id || null, sale_date, payment_method, notes, total_amount, created_by: req.user.id })
      .select().single()
    if (saleErr) throw saleErr

    const lineItems = items.map((i) => ({
      sale_id: sale.sale_id,
      inventory_id: i.inventory_id,
      product_id: i.product_id,
      qty_sold: i.qty_sold,
      unit_price: i.unit_price,
    }))
    const { error: itemErr } = await supabase.from('sale_items').insert(lineItems)
    if (itemErr) throw itemErr

    await Promise.all(items.map((i) => stockService.decrementStock(i.inventory_id, i.qty_sold)))

    res.status(201).json(addId(sale))
  } catch (err) { next(err) }
}
