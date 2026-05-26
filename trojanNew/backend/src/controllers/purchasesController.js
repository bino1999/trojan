const supabase = require('../config/supabase')
const stockService = require('../services/stockService')

exports.list = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('purchase_orders')
      .select('*, suppliers(name)')
      .order('order_date', { ascending: false })
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.get = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('purchase_orders')
      .select('*, suppliers(*), purchase_order_items(*, products(*))')
      .eq('purchase_order_id', req.params.id)
      .single()
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.create = async (req, res, next) => {
  try {
    const { supplier_id, order_date, invoice_number, notes, items } = req.body

    const { data: po, error: poErr } = await supabase
      .from('purchase_orders')
      .insert({ supplier_id, order_date, invoice_number, notes, status: 'pending', created_by: req.user.id })
      .select().single()
    if (poErr) throw poErr

    if (items?.length) {
      const lineItems = items.map((i) => ({
        purchase_order_id: po.purchase_order_id,
        product_id: i.product_id,
        qty_ordered: i.qty_ordered,
        qty_received: 0,
        company_price: i.company_price,
        selling_price: i.selling_price,
      }))
      const { error: itemErr } = await supabase.from('purchase_order_items').insert(lineItems)
      if (itemErr) throw itemErr
    }

    res.status(201).json(po)
  } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
  try {
    const { supplier_id, order_date, invoice_number, notes, items } = req.body
    const { data, error } = await supabase
      .from('purchase_orders')
      .update({ supplier_id, order_date, invoice_number, notes })
      .eq('purchase_order_id', req.params.id)
      .eq('status', 'pending')
      .select().single()
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.receive = async (req, res, next) => {
  try {
    const { received_date, items } = req.body
    await stockService.receivePurchaseOrder(req.params.id, received_date, items)
    res.json({ success: true })
  } catch (err) { next(err) }
}

exports.cancel = async (req, res, next) => {
  try {
    const { error } = await supabase
      .from('purchase_orders')
      .update({ status: 'cancelled' })
      .eq('purchase_order_id', req.params.id)
      .eq('status', 'pending')
    if (error) throw error
    res.json({ success: true })
  } catch (err) { next(err) }
}
