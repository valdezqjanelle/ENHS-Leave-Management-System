import api from './api'

export const getAdminDashboard = () => {
    return api.get('/admin/dashboard')
}

export const getEmployeeDashboard = () => {
    return api.get('/employee/dashboard')
}