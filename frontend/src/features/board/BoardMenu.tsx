import { useState } from 'react'
import { useEscapeKey } from '../../lib/useEscapeKey'

/**
 * General-purpose board actions menu - "Modifier les colonnes" is the only
 * entry today, but this is where future board-level actions belong.
 */
export function BoardMenu({
  isEditingColumns,
  onToggleEditColumns,
}: {
  isEditingColumns: boolean
  onToggleEditColumns: () => void
}) {
  const [isOpen, setIsOpen] = useState(false)
  useEscapeKey(() => setIsOpen(false))

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => setIsOpen((open) => !open)}
        aria-label="Menu du board"
        aria-expanded={isOpen}
        className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
      >
        ☰
      </button>

      {isOpen && (
        <>
          <div role="presentation" className="fixed inset-0 z-10" onClick={() => setIsOpen(false)} />
          <div className="absolute right-0 z-20 mt-2 w-56 rounded-md border border-gray-200 bg-white py-1 shadow-lg">
            <button
              type="button"
              onClick={() => {
                onToggleEditColumns()
                setIsOpen(false)
              }}
              className="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
            >
              {isEditingColumns ? 'Terminer la modification' : 'Modifier les colonnes'}
            </button>
          </div>
        </>
      )}
    </div>
  )
}
