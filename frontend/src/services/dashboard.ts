import api from './api'

// ---- existing exports (unchanged) ----
export const getAdminDashboard = () => {
    return api.get('/admin/dashboard')
}

export const getEmployeeDashboard = () => {
    return api.get('/employee/dashboard')
}

// ---- redesigned dashboards ----
export type DashboardPeriod = 'this_month' | 'last_3_months' | 'this_year' | 'all'

export const getAdminOverview = () => api.get('/admin/dashboard/overview')

export const getAdminAnalytics = (period: DashboardPeriod) =>
    api.get('/admin/dashboard/analytics', { params: { period } })

export const getAdminActivity = () => api.get('/admin/dashboard/activity')

export const getAdminCalendar = (month: string) =>
    api.get('/admin/dashboard/calendar', { params: { month } })

export const getEmployeeOverview = () => api.get('/employee/dashboard/overview')

export const getEmployeeCalendar = (month: string) =>
    api.get('/employee/dashboard/calendar', { params: { month } })
