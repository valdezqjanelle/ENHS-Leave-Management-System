import axios from "axios";

const API = "http://127.0.0.1:8000/api";

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

// Apply leave credit
export const applyLeaveCredit = async (id: number) => {
  const response = await axios.post(
    `${API}/leave-credits/${id}/apply`,
    {},
    authHeader()
  );

  return response.data;
};