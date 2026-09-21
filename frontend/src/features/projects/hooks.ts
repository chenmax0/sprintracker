import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { createMyProject, deleteProject, getProject, listMyProjects, renameProject } from './api'

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

export function useRenameProject(projectId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (name: string) => renameProject(projectId, name),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['projects'] })
      queryClient.invalidateQueries({ queryKey: ['projects', projectId] })
    },
  })
}

export function useDeleteProject(projectId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: () => deleteProject(projectId),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['projects'] }),
  })
}
