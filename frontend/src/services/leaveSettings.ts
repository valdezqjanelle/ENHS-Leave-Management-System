import axios from "axios";

const API = "http://127.0.0.1:8000/api";

const authHeader = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

export const getLeaveSettings = async () => {
  const response = await axios.get(
    `${API}/leave-settings`,
    authHeader()
  );

  return response.data;
};

export const updateLeaveSettings = async (data: {
  minimum_notice_days: number;
  require_documents: boolean;
  max_consecutive_days: number;
  general_policy: string;
}) => {
  const response = await axios.put(
    `${API}/leave-settings`,
    data,
    authHeader()
  );

  return response.data;
};