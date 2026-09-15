import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { createMyProject, getProject, listMyProjects } from './api'

export function useMyProjects() {
  return useQuery({ queryKey: ['projects'], queryFn: listMyProjects })
}

export function useProject(projectId: string) {
  return useQuery({
    queryKey: ['projects', projectId],
    queryFn: () => getProject(projectId),
    enabled: projectId !== '',
  })
}

export function useCreateMyProject() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (name: string) => createMyProject(name),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['projects'] }),
  })
}
