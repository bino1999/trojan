const supabase = require('../config/supabase')

exports.list = async (req, res, next) => {
  try {
    const { data, error } = await supabase.rpc('get_inventory_with_stats')
    if (error) throw error

    const result = (data ?? []).map(row => ({
      id:                row.inventory_id,
      inventory_id:      row.inventory_id,
      product_id:        row.product_id,
      supplier_id:       row.supplier_id,
      purchase_order_id: row.purchase_order_id,
      qty_in_stock:      row.qty_in_stock,
      qty_bought:        row.qty_bought,
      qty_sold:          Number(row.qty_sold),
      company_price:     row.company_price,
      selling_price:     row.selling_price,
      profit_margin:     row.profit_margin,
      reorder_level:     row.reorder_level,
      rack_no:           row.rack_no,
      bin_no:            row.bin_no,
      is_genuine:        row.is_genuine,
      discount_percent:  row.discount_percent,
      location:          row.location,
      batch_number:      row.batch_number,
      expiry_date:       row.expiry_date,
      created_at:        row.created_at,
      updated_at:        row.updated_at,
      products: {
        name:     row.product_name,
        sku:      row.product_sku,
        category: row.product_category,
        unit:     row.product_unit,
      },
      suppliers: {
        name: row.supplier_name,
      },
    }))

    res.json(result)
  } catch (err) { next(err) }
}

exports.get = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('inventory')
      .select('*, products(*), suppliers(*)')
      .eq('inventory_id', req.params.id)
      .single()
    if (error) throw error
    res.json({ ...data, id: data.inventory_id })
  } catch (err) { next(err) }
}
