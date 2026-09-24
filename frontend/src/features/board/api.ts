import { apiClient } from '../../lib/apiClient'

export interface Column {
  id: string
  projectId: string
  name: string
  position: number
}

export function listColumns(projectId: string): Promise<Column[]> {
  return apiClient.get(`/api/projects/${projectId}/columns`)
}

export function createColumn(projectId: string, name: string): Promise<Column> {
  return apiClient.post(`/api/projects/${projectId}/columns`, { name })
}

export function renameColumn(columnId: string, name: string): Promise<Column> {
  return apiClient.patch(`/api/columns/${columnId}`, { name })
}

export function reorderColumns(projectId: string, columnIds: string[]): Promise<Column[]> {
  return apiClient.put(`/api/projects/${projectId}/columns/order`, { columnIds })
}

export function deleteColumn(columnId: string): Promise<void> {
  return apiClient.delete(`/api/columns/${columnId}`)
}
