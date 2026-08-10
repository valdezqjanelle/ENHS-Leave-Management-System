import api from './api'

export const getLeaveSummary = () => {
  return api.get('/reports/leave-summary')
}

export const getLeaveCredits = () => {
  return api.get('/reports/leave-credits')
}

export const getEmployeesReport = () => {
  return api.get('/reports/employees')
}