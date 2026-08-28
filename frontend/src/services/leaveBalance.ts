import axios from "axios";

const API = "https://enhs-leave-management-system.onrender.com/api";

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