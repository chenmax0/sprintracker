import { useNavigate } from 'react-router'
import { useLogout, useMe } from '../auth/hooks'

export function DashboardPage() {
  const { data: user } = useMe()
  const logout = useLogout()
  const navigate = useNavigate()

  function handleLogout() {
    logout.mutate(undefined, { onSuccess: () => navigate('/login') })
  }

  return (
    <div className="mx-auto max-w-3xl p-8">
      <div className="mb-8 flex items-center justify-between">
        <h1 className="text-2xl font-semibold text-gray-900">Sprintracker</h1>
        <button
          type="button"
          onClick={handleLogout}
          className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
        >
          Déconnexion
        </button>
      </div>
      <p className="text-gray-600">Bienvenue, {user?.name}.</p>
    </div>
  )
}
