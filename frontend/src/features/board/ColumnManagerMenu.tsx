import { type FormEvent, useState } from 'react'
import { ApiError } from '../../lib/apiClient'
import type { Column } from './api'
import { CreateColumnModal } from './CreateColumnModal'
import { useColumns, useDeleteColumn, useRenameColumn } from './hooks'

function ColumnRow({
  column,
  onRename,
  onDelete,
  errorMessage,
}: {
  column: Column
  onRename: (name: string) => void
  onDelete: () => void
  errorMessage?: string
}) {
  const [isEditing, setIsEditing] = useState(false)
  const [name, setName] = useState(column.name)

  function commit(event: FormEvent) {
    event.preventDefault()
    setIsEditing(false)
    if (name.trim() && name !== column.name) {
      onRename(name.trim())
    } else {
      setName(column.name)
    }
  }

  return (
    <li>
      <div className="flex items-center gap-1 rounded px-2 py-1 hover:bg-gray-50">
        {isEditing ? (
          <form onSubmit={commit} className="flex-1">
            <input
              autoFocus
              value={name}
              onChange={(e) => setName(e.target.value)}
              onBlur={commit}
              className="w-full rounded border border-indigo-300 px-1.5 py-0.5 text-sm focus:outline-none"
            />
          </form>
        ) : (
          <button
            type="button"
            onClick={() => setIsEditing(true)}
            className="flex-1 truncate text-left text-sm text-gray-700"
          >
            {column.name}
          </button>
        )}
        <button
          type="button"
          onClick={onDelete}
          aria-label={`Supprimer la colonne "${column.name}"`}
          className="shrink-0 text-gray-300 hover:text-red-500"
        >
          ✕
        </button>
      </div>
      {errorMessage && <p className="px-2 pb-1 text-xs text-red-600">{errorMessage}</p>}
    </li>
  )
}

export function ColumnManagerMenu({ projectId }: { projectId: string }) {
  const { data: columns } = useColumns(projectId)
  const renameColumn = useRenameColumn(projectId)
  const deleteColumn = useDeleteColumn(projectId)
  const [isOpen, setIsOpen] = useState(false)
  const [isCreating, setIsCreating] = useState(false)
  const [failedColumnId, setFailedColumnId] = useState<string | null>(null)

  function handleDelete(columnId: string) {
    setFailedColumnId(null)
    deleteColumn.mutate(columnId, { onError: () => setFailedColumnId(columnId) })
  }

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => setIsOpen((open) => !open)}
        aria-label="Gérer les colonnes"
        aria-expanded={isOpen}
        className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
      >
        ☰
      </button>

      {isOpen && (
        <>
          <div role="presentation" className="fixed inset-0 z-10" onClick={() => setIsOpen(false)} />
          <div className="absolute right-0 z-20 mt-2 w-64 rounded-md border border-gray-200 bg-white py-2 shadow-lg">
            <p className="px-2 pb-1 text-xs font-semibold tracking-wide text-gray-400 uppercase">Colonnes</p>
            <ul className="flex flex-col">
              {columns?.map((column) => (
                <ColumnRow
                  key={column.id}
                  column={column}
                  onRename={(name) => renameColumn.mutate({ columnId: column.id, name })}
                  onDelete={() => handleDelete(column.id)}
                  errorMessage={
                    failedColumnId === column.id
                      ? deleteColumn.error instanceof ApiError
                        ? deleteColumn.error.message
                        : 'Suppression impossible'
                      : undefined
                  }
                />
              ))}
            </ul>
            <button
              type="button"
              onClick={() => {
                setIsCreating(true)
                setIsOpen(false)
              }}
              className="mt-1 w-full px-2 py-1.5 text-left text-sm font-medium text-indigo-600 hover:bg-indigo-50"
            >
              + Créer une colonne
            </button>
          </div>
        </>
      )}

      {isCreating && <CreateColumnModal projectId={projectId} onClose={() => setIsCreating(false)} />}
    </div>
  )
}
