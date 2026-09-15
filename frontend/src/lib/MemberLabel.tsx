import { useMe } from '../features/auth/hooks'
import type { MemberDirectory } from './memberDirectory'

export function MemberLabel({ memberId, directory }: { memberId: string; directory?: MemberDirectory }) {
  const { data: me } = useMe()
  const member = directory?.get(memberId)
  const isMe = me?.id === memberId

  if (member) {
    return (
      <span title={member.email}>
        {member.name}
        {isMe && ' (moi)'}
      </span>
    )
  }

  if (isMe) {
    return <span>Moi</span>
  }

  return <span title={memberId}>{memberId.slice(0, 8)}</span>
}
