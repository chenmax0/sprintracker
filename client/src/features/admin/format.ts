import type { Granularity } from './api'

// "2026-07-10T14:00:00" -> Date, read as plain local components so the label
// shows exactly the bucket the server computed in the admin's timezone.
function parseBucket(start: string): Date {
  const [datePart, timePart = '00:00:00'] = start.split('T')
  const [y, m, d] = datePart.split('-').map(Number)
  const [h] = timePart.split(':').map(Number)
  return new Date(y, m - 1, d, h)
}

export function formatAxisLabel(start: string, granularity: Granularity): string {
  const date = parseBucket(start)
  switch (granularity) {
    case 'hour':
      return `${date.getHours()} h`
    case 'month':
      return date.toLocaleDateString('fr-FR', { month: 'short', year: '2-digit' })
    default:
      return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
  }
}

export function formatBucketTitle(start: string, granularity: Granularity): string {
  const date = parseBucket(start)
  switch (granularity) {
    case 'hour':
      return `${date.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'long' })}, ${date.getHours()} h`
    case 'week':
      return `Semaine du ${date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })}`
    case 'month':
      return date.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })
    default:
      return date.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
  }
}
