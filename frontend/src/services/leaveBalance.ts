import axios from "axios";

const API = "http://127.0.0.1:8000/api";

const authHeader = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

export const getLeaveBalances = async () => {
  const response = await axios.get(
    `${API}/leave-balances`,
    authHeader()
  );

  return response.data;
};



export const deleteBalance = async (employee_id: number) => {
  const response = await axios.delete(
    `${API}/leave-balances/${employee_id}`,
    authHeader()
  );
  return response.data;
};


export const getLeaveBalanceByEmployeeId = async (employee_id: number) => {
  const response = await axios.get(
    `${API}/leave-balances/${employee_id}`,
    authHeader()
  );
  return response.data;
};