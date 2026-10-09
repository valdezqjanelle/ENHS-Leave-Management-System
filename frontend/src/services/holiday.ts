import api from './api';

export interface Holiday {
  id: number;
  date: string;
  name: string;
  type: string;
  description: string | null;
  is_active: boolean;
  created_at?: string;
  updated_at?: string;
}

export const getHolidays = async (params?: Record<string, string | number | boolean | undefined | null>) => {
  const { data } = await api.get('/holidays', { params });
  return data as Holiday[];
};

export const previewWorkingDays = async (startDate: string, endDate: string) => {
  const { data } = await api.post('/holidays/preview', { start_date: startDate, end_date: endDate });
  return data;
};

export const createHoliday = async (payload: Partial<Holiday> & { date: string; name: string; type: string; is_active: boolean; description?: string | null }) => {
  const { data } = await api.post('/admin/holidays', payload);
  return data;
};

export const updateHoliday = async (id: number, payload: Partial<Holiday>) => {
  const { data } = await api.put(`/admin/holidays/${id}`, payload);
  return data;
};

export const deleteHoliday = async (id: number) => {
  const { data } = await api.delete(`/admin/holidays/${id}`);
  return data;
};
