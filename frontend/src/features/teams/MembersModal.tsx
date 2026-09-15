import { type FormEvent, useState } from 'react'
import { ApiError } from '../../lib/apiClient'
import { Modal } from '../../lib/Modal'
import { useAddTeamMember, useTeamMembers } from './hooks'

export function MembersModal({ teamId, onClose }: { teamId: string; onClose: () => void }) {
  const { data: members } = useTeamMembers(teamId)
  const addMember = useAddTeamMember(teamId)
  const [email, setEmail] = useState('')

  function handleSubmit(event: FormEvent) {
    event.preventDefault()
    addMember.mutate(email, { onSuccess: () => setEmail('') })
  }

  return (
    <Modal title="Membres" onClose={onClose}>
      <ul className="mb-4 flex flex-col gap-2">
        {members?.map((member) => (
          <li key={member.memberId} className="flex items-center gap-2 text-sm text-gray-700">
            <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-100 text-[11px] font-semibold text-gray-600">
              {member.name.slice(0, 1).toUpperCase()}
            </span>
            <span>{member.name}</span>
            <span className="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500">{member.role}</span>
          </li>
        ))}
      </ul>
      <form onSubmit={handleSubmit} className="flex gap-2">
        <input
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          placeholder="Email à inviter"
          required
          className="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
        />
        <button
          type="submit"
          disabled={addMember.isPending}
          className="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
        >
          Inviter
        </button>
      </form>
      {addMember.isError && (
        <p className="mt-2 text-sm text-red-600">
          {addMember.error instanceof ApiError ? addMember.error.message : "Erreur lors de l'invitation"}
        </p>
      )}
    </Modal>
  )
}
