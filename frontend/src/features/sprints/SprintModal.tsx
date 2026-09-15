import { type FormEvent, useState } from 'react'
import { ApiError } from '../../lib/apiClient'
import { Modal } from '../../lib/Modal'
import { useCompleteSprint, useLaunchSprint } from './hooks'

interface SprintModalProps {
  projectId: string
  /** When set, this modal completes that sprint and launches the next one instead of launching the first. */
  completingSprintId?: string
  onClose: () => void
}

export function SprintModal({ projectId, completingSprintId, onClose }: SprintModalProps) {
  const launchSprint = useLaunchSprint(projectId)
  const completeSprint = useCompleteSprint(projectId)
  const [startDate, setStartDate] = useState('')
  const [endDate, setEndDate] = useState('')

  const isCompleting = completingSprintId !== undefined
  const mutation = isCompleting ? completeSprint : launchSprint

  function handleSubmit(event: FormEvent) {
    event.preventDefault()

    if (isCompleting) {
      completeSprint.mutate(
        { sprintId: completingSprintId, nextStartDate: startDate, nextEndDate: endDate },
        { onSuccess: onClose },
      )
    } else {
      launchSprint.mutate({ startDate, endDate }, { onSuccess: onClose })
    }
  }

  return (
    <Modal title={isCompleting ? 'Terminer le sprint' : 'Lancer un sprint'} onClose={onClose}>
      {isCompleting && (
        <p className="mb-4 text-sm text-gray-600">
          Les tickets non terminés du sprint actuel seront reportés sur le nouveau sprint.
        </p>
      )}
      <form onSubmit={handleSubmit} className="flex flex-col gap-4">
        <div className="flex flex-wrap gap-2">
          <label className="flex flex-1 flex-col gap-1">
            <span className="text-xs text-gray-500">Début</span>
            <input
              type="date"
              value={startDate}
              onChange={(e) => setStartDate(e.target.value)}
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
              onChange={(e) => setEndDate(e.target.value)}
              required
              className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
            />
          </label>
        </div>
        {mutation.isError && (
          <p className="text-sm text-red-600">
            {mutation.error instanceof ApiError ? mutation.error.message : 'Erreur lors de cette action'}
          </p>
        )}
        <button
          type="submit"
          disabled={mutation.isPending}
          className="self-end rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
        >
          {isCompleting ? 'Terminer le sprint' : 'Lancer le sprint'}
        </button>
      </form>
    </Modal>
  )
}
