import type { TeamMember } from '../features/teams/api'

export type MemberDirectory = Map<string, TeamMember>

export function toMemberDirectory(members: TeamMember[] | undefined): MemberDirectory {
  return new Map((members ?? []).map((member) => [member.memberId, member]))
}
