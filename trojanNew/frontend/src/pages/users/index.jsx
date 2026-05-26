export default function Users() {
  return (
    <div>
      <h1 className="text-2xl font-semibold mb-1">User Management</h1>
      <p className="text-muted-foreground text-sm mb-6">Manage staff accounts and roles (Admin only)</p>
      {/* TODO: user list with role filter */}
      {/* TODO: invite user (Supabase Auth invite) / assign role */}
      {/* TODO: deactivate user */}
    </div>
  )
}
