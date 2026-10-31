export const STALE_AFTER_DAYS = 90

export function ageInDays(createdAt: string, now: Date = new Date()): number {
  return Math.max(0, Math.floor((now.getTime() - new Date(createdAt).getTime()) / 86_400_000))
}

export function formatAge(days: number): string {
  if (days < 1) return "Aujourd'hui"
  if (days < 60) return `il y a ${days} j`
  if (days < 730) return `il y a ${Math.floor(days / 30)} mois`
  return `il y a ${Math.floor(days / 365)} ans`
}
