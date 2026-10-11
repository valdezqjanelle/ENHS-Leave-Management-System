import api from './api'

export const getLeaveSummary = (params?: {
  start_date?: string
  end_date?: string
  include_inactive?: boolean
  department_id?: number | string // a department id, or 'unassigned'
  status?: 'approved' | 'pending' | 'disapproved'
  leave_type_id?: number | string
  search?: string // employee name words
}) => {
  return api.get('/reports/leave-summary', {
    params
  })
}

// Current balance snapshot: balances are not historical, so no date range is accepted.
export const getLeaveCredits = () => {
  return api.get('/reports/leave-credits')
}

export const getEmployeesReport = (params?: {
  start_date?: string
  end_date?: string
}) => {
  return api.get('/reports/employees', {
    params
  })
}