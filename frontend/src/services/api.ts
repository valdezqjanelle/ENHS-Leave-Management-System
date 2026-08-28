import axios from 'axios'

const api = axios.create({
  baseURL: 'https://enhs-leave-management-system.onrender.com/api',
  headers: {
    Accept: 'application/json',
  },
})
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')

  console.log("API REQUEST:", config.url)
  console.log("TOKEN EXISTS:", !!token)
  console.log("TOKEN:", token)

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

export default api