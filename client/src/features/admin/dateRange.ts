import type { Granularity } from './api'

export interface RangePreset {
  id: string
  label: string
  /** Number of local days ending today, today included. */
  days: number
}

export const RANGE_PRESETS: RangePreset[] = [
  { id: 'today', label: "Aujourd'hui", days: 1 },
  { id: '7d', label: '7 jours', days: 7 },
  { id: '30d', label: '30 jours', days: 30 },
  { id: '90d', label: '90 jours', days: 90 },
  { id: '12m', label: '12 mois', days: 365 },
]

export const GRANULARITIES: { id: Granularity; label: string }[] = [
  { id: 'hour', label: 'Heure' },
  { id: 'day', label: 'Jour' },
  { id: 'week', label: 'Semaine' },
  { id: 'month', label: 'Mois' },
]

// Mirrors the server's Granularity::maxSpanInDays(): beyond it the API answers 422.
export function maxDays(granularity: Granularity): number {
  return granularity === 'hour' ? 31 : 731
}

function toDateString(date: Date): string {
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${date.getFullYear()}-${month}-${day}`
}

export function rangeFor(days: number, now: Date = new Date()): { from: string; to: string } {
  const from = new Date(now.getFullYear(), now.getMonth(), now.getDate() - (days - 1))
  return { from: toDateString(from), to: toDateString(now) }
}

export function browserTimezone(): string {
  return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC'
}
