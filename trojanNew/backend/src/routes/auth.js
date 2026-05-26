const router = require('express').Router()
const supabase = require('../config/supabase')

router.post('/', async (req, res, next) => {
  try {
    const { email, password } = req.body
    const { data, error } = await supabase.auth.signInWithPassword({ email, password })
    if (error) return res.status(401).json({ error: error.message })

    const { data: profile } = await supabase
      .from('user_profiles')
      .select('role')
      .eq('user_id', data.user.id)
      .single()

    res.json({
      user: { id: data.user.id, email: data.user.email },
      token: data.session.access_token,
      role: profile?.role ?? null,
    })
  } catch (err) {
    next(err)
  }
})

module.exports = router
