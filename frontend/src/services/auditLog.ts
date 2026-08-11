import api from './api'

export interface AuditLogFilters {
  action?: string
  date_from?: string
  date_to?: string
  page?: number
  per_page?: number
}

export const getAuditLogs = (filters: AuditLogFilters = {}) => {
  return api.get('/audit-logs', { params: filters })
}

export const getAuditLogActions = () => {
  return api.get('/audit-logs/actions')
}