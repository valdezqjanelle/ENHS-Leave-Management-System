import api from "./api";
export interface ManagedPosition {
  id: number;
  code: string;
  name: string;
  type: string;
  salary_grade: string | number | null;
  is_active: boolean;
  assigned_records: number;
}
export interface ScheduleHeader {
  id: number;
  name: string;
  effective_date: string | null;
  status: string;
  source_reference: string | null;
  revision: number;
  rate_count?: number;
}
export interface ScheduleRate {
  salary_grade: number;
  step: number;
  salary: string | number | null;
}
export interface ScheduleDetails {
  schedule: ScheduleHeader;
  rates: ScheduleRate[];
}
export interface SchedulePreview {
  can_activate: boolean;
  errors: string[];
  rate_count: number;
  active_schedule_id: number | null;
  changes: {
    salary_grade: number;
    step: number;
    before: string | number | null;
    after: string | number | null;
  }[];
}
export const managedPositions = async (): Promise<ManagedPosition[]> =>
  (await api.get("/admin/position-management")).data;
export const savePosition = async (id: number | null, data: unknown) =>
  id
    ? (await api.put(`/admin/position-management/${id}`, data)).data
    : (await api.post("/admin/position-management", data)).data;
export const salarySchedules = async (): Promise<ScheduleHeader[]> =>
  (await api.get("/admin/salary-schedules")).data;
export const salarySchedule = async (id: number): Promise<ScheduleDetails> =>
  (await api.get(`/admin/salary-schedules/${id}`)).data;
export const createSalarySchedule = async (
  data: unknown,
): Promise<ScheduleDetails> =>
  (await api.post("/admin/salary-schedules", data)).data;
export const saveSalarySchedule = async (
  id: number,
  data: unknown,
): Promise<ScheduleDetails> =>
  (await api.put(`/admin/salary-schedules/${id}`, data)).data;
export const previewSalarySchedule = async (
  id: number,
  revision: number,
): Promise<SchedulePreview> =>
  (await api.post(`/admin/salary-schedules/${id}/preview`, { revision })).data;
export const activateSalarySchedule = async (
  id: number,
  revision: number,
  expected_active_id: number | null,
) =>
  (
    await api.post(`/admin/salary-schedules/${id}/activate`, {
      revision,
      expected_active_id,
      confirm: true,
    })
  ).data;
export const deleteSalaryDraft = async (id: number, revision: number) =>
  (await api.delete(`/admin/salary-schedules/${id}`, { data: { revision } }))
    .data;
export function compensationError(error: any): string {
  const errors = error.response?.data?.errors;
  return errors
    ? Object.values(errors).flat().join(" ")
    : error.response?.data?.message ||
        "Unable to complete the request. Please try again.";
}

// Status-only update: preserve the position's current name, code, type and grade.
export const setPositionStatus = async (id: number, is_active: boolean): Promise<{ message: string; data: ManagedPosition }> =>
  (await api.put(`/admin/position-management/${id}`, { is_active })).data;
