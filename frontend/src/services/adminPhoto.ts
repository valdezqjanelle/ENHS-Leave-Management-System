import api from './api'

export const getAdminPhoto = async () => (await api.get('/admin/profile/photo')).data
export const removeAdminPhoto = async () => (await api.delete('/admin/profile/photo')).data
export const uploadAdminPhoto = async (photo: File) => {
  const data = new FormData()
  data.append('photo', photo)
  return (await api.post('/admin/profile/photo', data)).data
}
