import { Navigate, Outlet } from 'react-router'
import { useMe } from './hooks'

export function RequireAuth() {
  const { data: user, isLoading, isError } = useMe()

  if (isLoading) {
    return <div className="p-8 text-center text-gray-500">Chargement…</div>
  }

  if (isError || !user) {
    return <Navigate to="/login" replace />
  }

  return <Outlet />
}
