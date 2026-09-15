import { useMe } from '../features/auth/hooks'

/**
 * The API only exposes member/reporter/assignee/author as raw ids - there's no
 * "look up a user's name by id" endpoint yet. Show "Moi" for the current user
 * and a shortened id otherwise, rather than a full UUID.
 */
export function MemberLabel({ memberId }: { memberId: string }) {
  const { data: me } = useMe()

  if (me?.id === memberId) {
    return <span>Moi</span>
  }

  return <span title={memberId}>{memberId.slice(0, 8)}</span>
}
