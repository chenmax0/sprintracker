import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { createProject, getProject, listProjects } from './api'

export function useProjects(teamId: string) {
  return useQuery({ queryKey: ['teams', teamId, 'projects'], queryFn: () => listProjects(teamId) })
}

export function useProject(projectId: string) {
  return useQuery({ queryKey: ['projects', projectId], queryFn: () => getProject(projectId) })
}

export function useCreateProject(teamId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (name: string) => createProject(teamId, name),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['teams', teamId, 'projects'] }),
  })
}
