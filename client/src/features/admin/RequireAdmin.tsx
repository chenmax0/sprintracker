import { Navigate, Outlet } from 'react-router'
import { useMe } from '../auth/hooks'

// The API enforces ROLE_ADMIN on /api/admin; this only keeps non-admins from
// landing on a page that can't load anything.
export function RequireAdmin() {
  const { data: user } = useMe()

  if (!user?.isAdmin) {
    return <Navigate to="/app" replace />
  }

  return <Outlet />
}
