import { type FormEvent, useState } from 'react'
import { ApiError } from '../../lib/apiClient'
import { Modal } from '../../lib/Modal'
import { useLaunchSprint } from './hooks'

const DURATION_KEY = 'sprintracker.sprintWeeks'
const DEFAULT_WEEKS = 2
const WEEK_OPTIONS = [1, 2, 3, 4]

function toDateString(date: Date): string {
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${date.getFullYear()}-${month}-${day}`
}

function addWeeks(dateString: string, weeks: number): string {
  const [y, m, d] = dateString.split('-').map(Number)
  return toDateString(new Date(y, m - 1, d + weeks * 7))
}

// The user's preferred sprint length, remembered across sprints in this browser.
function loadWeeks(): number {
  try {
    const stored = Number(localStorage.getItem(DURATION_KEY))
    return WEEK_OPTIONS.includes(stored) ? stored : DEFAULT_WEEKS
  } catch {
    return DEFAULT_WEEKS
  }
}

function saveWeeks(weeks: number) {
  try {
    localStorage.setItem(DURATION_KEY, String(weeks))
  } catch {
    // storage unavailable: the choice just won't be remembered
  }
}

interface SprintModalProps {
  projectId: string
  onClose: () => void
}

export function SprintModal({ projectId, onClose }: SprintModalProps) {
  const launchSprint = useLaunchSprint(projectId)
  const [startDate, setStartDate] = useState(() => toDateString(new Date()))
  const [weeks, setWeeks] = useState(loadWeeks)
  // Set only when the user types an end date by hand; otherwise the end follows start + duration.
  const [customEndDate, setCustomEndDate] = useState<string | null>(null)
  const endDate = customEndDate ?? addWeeks(startDate, weeks)

  function handleStartChange(value: string) {
    setStartDate(value)
    setCustomEndDate(null)
  }

  function handleWeeksChange(value: number) {
    setWeeks(value)
    setCustomEndDate(null)
    saveWeeks(value)
  }

  function handleSubmit(event: FormEvent) {
    event.preventDefault()

    launchSprint.mutate({ startDate, endDate }, { onSuccess: onClose })
  }

  return (
    <Modal title="Lancer un sprint" onClose={onClose}>
      <form onSubmit={handleSubmit} className="flex flex-col gap-4">
        <div className="flex flex-wrap gap-2">
          <label className="flex flex-1 flex-col gap-1">
            <span className="text-xs text-gray-500">Début</span>
            <input
              type="date"
              value={startDate}
              onChange={(e) => handleStartChange(e.target.value)}
              autoFocus
              required
              className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
            />
          </label>
          <label className="flex flex-1 flex-col gap-1">
            <span className="text-xs text-gray-500">Fin</span>
            <input
              type="date"
              value={endDate}
              onChange={(e) => setCustomEndDate(e.target.value)}
              required
              className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
            />
          </label>
        </div>
        <label className="flex items-center gap-2 text-sm text-gray-600">
          Durée par défaut
          <select
            value={weeks}
            onChange={(e) => handleWeeksChange(Number(e.target.value))}
            className="rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-indigo-500 focus:outline-none"
          >
            {WEEK_OPTIONS.map((w) => (
              <option key={w} value={w}>
                {w} semaine{w > 1 ? 's' : ''}
              </option>
            ))}
          </select>
        </label>
        {launchSprint.isError && (
          <p className="text-sm text-red-600">
            {launchSprint.error instanceof ApiError ? launchSprint.error.message : 'Erreur lors de cette action'}
          </p>
        )}
        <button
          type="submit"
          disabled={launchSprint.isPending}
          className="self-end rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
        >
          Lancer le sprint
        </button>
      </form>
    </Modal>
  )
}
