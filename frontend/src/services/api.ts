import axios from 'axios'

const api = axios.create({
baseURL: 'https://enhs-leave-management-system.onrender.com/api',
  headers: {

    Accept: 'application/json',
  },
})

// Automatically attach the token if it exists
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

export default api