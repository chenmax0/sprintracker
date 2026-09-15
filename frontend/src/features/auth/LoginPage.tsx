import { type FormEvent, useState } from 'react'
import { Link, useNavigate } from 'react-router'
import { ApiError } from '../../lib/apiClient'
import { useLogin } from './hooks'

export function LoginPage() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const login = useLogin()
  const navigate = useNavigate()

  function handleSubmit(event: FormEvent) {
    event.preventDefault()
    login.mutate(
      { email, password },
      { onSuccess: () => navigate('/app') },
    )
  }

  return (
    <div className="mx-auto mt-24 max-w-sm">
      <h1 className="mb-6 text-2xl font-semibold text-gray-900">Connexion</h1>
      <form onSubmit={handleSubmit} className="flex flex-col gap-4">
        <label className="flex flex-col gap-1">
          <span className="text-sm font-medium text-gray-700">Email</span>
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          />
        </label>
        <label className="flex flex-col gap-1">
          <span className="text-sm font-medium text-gray-700">Mot de passe</span>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          />
        </label>
        {login.isError && (
          <p className="text-sm text-red-600">
            {login.error instanceof ApiError ? login.error.message : 'Erreur de connexion'}
          </p>
        )}
        <button
          type="submit"
          disabled={login.isPending}
          className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
        >
          {login.isPending ? 'Connexion…' : 'Se connecter'}
        </button>
      </form>
      <p className="mt-4 text-sm text-gray-600">
        Pas de compte ?{' '}
        <Link to="/register" className="text-indigo-600 hover:underline">
          Créer un compte
        </Link>
      </p>
    </div>
  )
}
