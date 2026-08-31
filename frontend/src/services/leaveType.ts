import axios from "axios";

const API =
  "https://enhs-leave-management-system.onrender.com/api";

const authHeader = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

export interface LeaveType {
  leave_type_id: number;
  code: string;
  leave_type_name: string;
  legal_basis: string | null;
  created_at?: string;
  updated_at?: string;
}

export interface LeaveTypePayload {
  code: string;
  leave_type_name: string;
  legal_basis: string;
}

// GET ALL
export const getLeaveTypes = async (): Promise<
  LeaveType[]
> => {
  const response = await axios.get<LeaveType[]>(
    `${API}/leave-types`,
    authHeader(),
  );

  return response.data;
};

// CREATE
export const createLeaveType = async (
  data: LeaveTypePayload,
): Promise<LeaveType> => {
  const response = await axios.post(
    `${API}/leave-types`,
    data,
    authHeader(),
  );

  return response.data.data;
};

// UPDATE
export const updateLeaveType = async (
  id: number,
  data: LeaveTypePayload,
): Promise<LeaveType> => {
  const response = await axios.put(
    `${API}/leave-types/${id}`,
    data,
    authHeader(),
  );

  return response.data.data;
};

// DELETE
export const deleteLeaveType = async (
  id: number,
): Promise<void> => {
  await axios.delete(
    `${API}/leave-types/${id}`,
    authHeader(),
  );
};