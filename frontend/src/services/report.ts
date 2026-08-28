import api from './api'

export const getLeaveSummary = (params?: {
  start_date?: string
  end_date?: string
}) => {
  return api.get('/reports/leave-summary', {
    params
  })
}

export const getLeaveCredits = (params?: {
  start_date?: string
  end_date?: string
}) => {
  return api.get('/reports/leave-credits', {
    params
  })
}

export const getEmployeesReport = (params?: {
  start_date?: string
  end_date?: string
}) => {
  return api.get('/reports/employees', {
    params
  })
}