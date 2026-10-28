import { Link, Outlet, useNavigate } from 'react-router'
import { useLogout, useMe } from '../auth/hooks'

export function AppLayout() {
  const { data: user } = useMe()
  const logout = useLogout()
  const navigate = useNavigate()

  function handleLogout() {
    logout.mutate(undefined, { onSuccess: () => navigate('/login') })
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <header className="sticky top-0 z-10 border-b border-gray-200 bg-white/90 backdrop-blur-sm">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-3.5">
          <Link to="/app" className="flex items-center gap-2.5 text-base font-semibold text-gray-900">
            <span className="flex h-7 w-7 items-center justify-center rounded-md bg-indigo-600 text-sm font-bold text-white">
              S
            </span>
            Sprintracker
          </Link>
          <div className="flex items-center gap-3">
            {user && (
              <span
                className="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700"
                title={user.name}
              >
                {user.name.slice(0, 1).toUpperCase()}
              </span>
            )}
            <button
              type="button"
              onClick={handleLogout}
              className="rounded-md px-3 py-1.5 text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-700"
            >
              Déconnexion
            </button>
          </div>
        </div>
      </header>
      <main className="px-6 py-10">
        <Outlet />
      </main>
    </div>
  )
}
