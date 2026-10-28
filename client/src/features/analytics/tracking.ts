import { apiClient } from '../../lib/apiClient'

const VISITOR_ID_KEY = 'sprintracker.visitorId'
const MIN_INTERVAL_MS = 5 * 60 * 1000
const HEARTBEAT_MS = 10 * 60 * 1000

let lastSentAt = 0
let cachedVisitorId: string | null = null

// A random id with no personal data in it. It lives in localStorage so the same
// browser keeps counting as one visitor across days; if storage is blocked the
// id only lasts for the page's lifetime.
function getVisitorId(): string {
  if (cachedVisitorId) {
    return cachedVisitorId
  }

  try {
    const stored = localStorage.getItem(VISITOR_ID_KEY)
    if (stored) {
      cachedVisitorId = stored
      return stored
    }
  } catch {
    // storage unavailable: fall through to a fresh in-memory id
  }

  const id = crypto.randomUUID()
  cachedVisitorId = id
  try {
    localStorage.setItem(VISITOR_ID_KEY, id)
  } catch {
    // ignore: the visitor is still counted for this page's lifetime
  }
  return id
}

/**
 * Tells the server this visitor is active. The server stores one row per
 * visitor per hour, so calls are throttled client-side. `force` skips the
 * throttle (used right after login, to link the visitor to their account).
 * Tracking must never break the app: errors are swallowed.
 */
export function trackVisit({ force = false }: { force?: boolean } = {}): void {
  const now = Date.now()
  if (!force && now - lastSentAt < MIN_INTERVAL_MS) {
    return
  }
  lastSentAt = now

  apiClient.post('/api/track', { visitorId: getVisitorId() }).catch(() => {})
}

/** Keeps long sessions counted in every hour they stay open. Returns a cleanup. */
export function startHeartbeat(): () => void {
  const id = window.setInterval(() => {
    if (document.visibilityState === 'visible') {
      trackVisit()
    }
  }, HEARTBEAT_MS)

  return () => window.clearInterval(id)
}
