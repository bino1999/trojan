const router = require('express').Router()
const supabase = require('../config/supabase')
const auth = require('../middleware/auth')

router.post('/login', async (req, res, next) => {
  try {
    const { email, password } = req.body
    const { data, error } = await supabase.auth.signInWithPassword({ email, password })
    if (error) return res.status(401).json({ error: error.message })

    const { data: profile } = await supabase
      .from('user_profiles')
      .select('role')
      .eq('user_id', data.user.id)
      .single()

    if (!profile?.role) {
      // First-run: if no profiles exist at all, auto-create this user as admin
      const { count } = await supabase
        .from('user_profiles')
        .select('*', { count: 'exact', head: true })

      if (count === 0) {
        await supabase.from('user_profiles').insert({
          user_id: data.user.id,
          email: data.user.email,
          role: 'admin',
        })
        return res.json({
          user: { id: data.user.id, email: data.user.email },
          token: data.session.access_token,
          role: 'admin',
        })
      }

      return res.status(403).json({
        error: 'Your account has no role assigned. Ask an admin to invite you through the Users page.',
      })
    }

    res.json({
      user: { id: data.user.id, email: data.user.email },
      token: data.session.access_token,
      role: profile.role,
    })
  } catch (err) {
    next(err)
  }
})

router.get('/me', auth, async (req, res, next) => {
  try {
    res.json({ id: req.user.id, email: req.user.email, role: req.role })
  } catch (err) {
    next(err)
  }
})

module.exports = router
