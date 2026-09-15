import { type FormEvent, useState } from 'react'
import { Link, useNavigate } from 'react-router'
import { ApiError } from '../../lib/apiClient'
import { useLogin, useRegister } from './hooks'

export function RegisterPage() {
  const [email, setEmail] = useState('')
  const [name, setName] = useState('')
  const [password, setPassword] = useState('')
  const register = useRegister()
  const login = useLogin()
  const navigate = useNavigate()

  function handleSubmit(event: FormEvent) {
    event.preventDefault()
    register.mutate(
      { email, name, password },
      {
        onSuccess: () => login.mutate({ email, password }, { onSuccess: () => navigate('/app') }),
      },
    )
  }

  const error = register.error ?? login.error
  const isPending = register.isPending || login.isPending

  return (
    <div className="mx-auto mt-24 max-w-sm">
      <h1 className="mb-6 text-2xl font-semibold text-gray-900">Créer un compte</h1>
      <form onSubmit={handleSubmit} className="flex flex-col gap-4">
        <label className="flex flex-col gap-1">
          <span className="text-sm font-medium text-gray-700">Nom</span>
          <input
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
            className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          />
        </label>
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
            minLength={8}
            className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          />
        </label>
        {error && (
          <p className="text-sm text-red-600">
            {error instanceof ApiError ? error.message : "Erreur lors de l'inscription"}
          </p>
        )}
        <button
          type="submit"
          disabled={isPending}
          className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
        >
          {isPending ? 'Création…' : 'Créer le compte'}
        </button>
      </form>
      <p className="mt-4 text-sm text-gray-600">
        Déjà un compte ?{' '}
        <Link to="/login" className="text-indigo-600 hover:underline">
          Se connecter
        </Link>
      </p>
    </div>
  )
}
