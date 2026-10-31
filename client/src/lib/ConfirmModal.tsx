import { Modal } from './Modal'

interface ConfirmModalProps {
  title: string
  message: string
  confirmLabel?: string
  /** 'danger' (default) for deletions, 'primary' for harmless confirmations. */
  tone?: 'danger' | 'primary'
  isPending?: boolean
  errorMessage?: string | null
  onConfirm: () => void
  onClose: () => void
}

/**
 * Generic "are you sure?" dialog for destructive actions - every deletion
 * in the app should go through this instead of acting immediately on click.
 */
export function ConfirmModal({
  title,
  message,
  confirmLabel = 'Supprimer',
  tone = 'danger',
  isPending = false,
  errorMessage,
  onConfirm,
  onClose,
}: ConfirmModalProps) {
  return (
    <Modal title={title} onClose={onClose}>
      <p className="mb-4 text-sm text-gray-600">{message}</p>
      {errorMessage && <p className="mb-4 text-sm text-red-600">{errorMessage}</p>}
      <div className="flex justify-end gap-2">
        <button
          type="button"
          onClick={onClose}
          className="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
        >
          Annuler
        </button>
        <button
          type="button"
          onClick={onConfirm}
          disabled={isPending}
          className={`rounded-md px-4 py-2 text-sm font-medium text-white disabled:opacity-50 ${
            tone === 'danger' ? 'bg-red-600 hover:bg-red-500' : 'bg-indigo-600 hover:bg-indigo-500'
          }`}
        >
          {confirmLabel}
        </button>
      </div>
    </Modal>
  )
}
