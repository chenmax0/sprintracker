import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { createColumn, deleteColumn, listColumns, renameColumn, reorderColumns } from './api'

export function useColumns(projectId: string) {
  return useQuery({
    queryKey: ['projects', projectId, 'columns'],
    queryFn: () => listColumns(projectId),
    enabled: projectId !== '',
  })
}

export function useCreateColumn(projectId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (name: string) => createColumn(projectId, name),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['projects', projectId, 'columns'] }),
  })
}

export function useRenameColumn(projectId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ columnId, name }: { columnId: string; name: string }) => renameColumn(columnId, name),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['projects', projectId, 'columns'] }),
  })
}

export function useReorderColumns(projectId: string) {
  const queryClient = useQueryClient()
  const queryKey = ['projects', projectId, 'columns']

  return useMutation({
    mutationFn: (columnIds: string[]) => reorderColumns(projectId, columnIds),
    onSuccess: (columns) => queryClient.setQueryData(queryKey, columns),
    onSettled: () => queryClient.invalidateQueries({ queryKey }),
  })
}

export function useDeleteColumn(projectId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (columnId: string) => deleteColumn(columnId),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['projects', projectId, 'columns'] }),
  })
}
