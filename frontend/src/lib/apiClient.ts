const API_URL = import.meta.env.VITE_API_URL

export class ApiError extends Error {
  readonly status: number
  readonly errors: string[]

  constructor(message: string, status: number, errors: string[]) {
    super(message)
    this.status = status
    this.errors = errors
  }
}

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  const response = await fetch(`${API_URL}${path}`, {
    ...options,
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
  })

  if (response.status === 204) {
    return undefined as T
  }

  const body = await response.json().catch(() => null)

  if (!response.ok) {
    const errors: string[] = body?.errors ?? ['Something went wrong.']
    throw new ApiError(errors[0], response.status, errors)
  }

  return body as T
}

function withBody<T>(method: 'POST' | 'PATCH' | 'PUT', path: string, data?: unknown) {
  return request<T>(path, { method, body: data !== undefined ? JSON.stringify(data) : undefined })
}

export const apiClient = {
  get: <T>(path: string) => request<T>(path),
  post: <T>(path: string, data?: unknown) => withBody<T>('POST', path, data),
  patch: <T>(path: string, data?: unknown) => withBody<T>('PATCH', path, data),
  put: <T>(path: string, data?: unknown) => withBody<T>('PUT', path, data),
  delete: <T>(path: string) => request<T>(path, { method: 'DELETE' }),
}
