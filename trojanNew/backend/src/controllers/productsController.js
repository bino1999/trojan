const supabase = require('../config/supabase')

exports.list = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('products')
      .select('*')
      .order('name')
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.get = async (req, res, next) => {
  try {
    const { data, error } = await supabase
      .from('products')
      .select('*')
      .eq('product_id', req.params.id)
      .single()
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.create = async (req, res, next) => {
  try {
    const { name, category, unit, description } = req.body
    const { data, error } = await supabase
      .from('products')
      .insert({ name, category, unit, description })
      .select()
      .single()
    if (error) throw error
    res.status(201).json(data)
  } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
  try {
    const { name, category, unit, description } = req.body
    const { data, error } = await supabase
      .from('products')
      .update({ name, category, unit, description })
      .eq('product_id', req.params.id)
      .select()
      .single()
    if (error) throw error
    res.json(data)
  } catch (err) { next(err) }
}

exports.deactivate = async (req, res, next) => {
  try {
    const { error } = await supabase
      .from('products')
      .update({ is_active: false })
      .eq('product_id', req.params.id)
    if (error) throw error
    res.status(204).end()
  } catch (err) { next(err) }
}
