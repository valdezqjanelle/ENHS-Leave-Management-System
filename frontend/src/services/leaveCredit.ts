import axios from "axios";

const API = "https://enhs-leave-management-system.onrender.com/api";

const authHeader = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

// Get all employees
export const getEmployees = async () => {
  const response = await axios.get(`${API}/employees`, authHeader());
  return response.data;
};

// Get all leave credits
export const getLeaveCredits = async () => {
  const response = await axios.get(`${API}/leave-credits`, authHeader());
  return response.data;
};

// Add leave credit
export const addLeaveCredit = async (data: any) => {
  const response = await axios.post(
    `${API}/leave-credits`,
    data,
    authHeader()
  );

  return response.data;
};

interface ApplyLeaveCreditPayload {
  credits_id: number;
  leave_type: string;
  days: number;
  split: boolean;
  vacation_days: number;
  sick_days: number;
}

// Apply leave credit
export const applyLeaveCredit = async (data: ApplyLeaveCreditPayload) => {
  const response = await axios.post(
    `${API}/leave-credits/${data.credits_id}/apply`,
    data,
    authHeader()
  );

  return response.data;
};

export const deleteLeaveCredit = async (id: number) => {
  const response = await axios.delete(
    `${API}/leave-credits/${id}`,
    authHeader()
  );

  return response.data;
};