const supabase = require('../config/supabase')

exports.list = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('inventory')
      .select('*, products(name, category, unit), suppliers(name)')
      .order('created_at', { ascending: false })
    if (error) throw error
    res.json(data)
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
    res.json(data)
  } catch (err) { next(err) }
}
