import axios from "axios";

const API = "https://enhs-leave-management-system.onrender.com/api";

const authHeader = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

export const getSystemSettings = async () => {
  const response = await axios.get(
    `${API}/system-settings`,
    authHeader()
  );

  return response.data;
};

export const updateSystemSettings = async (data: {
  system_name: string;
  school_name: string;
  system_description: string;
  about_us: string;
  contact_email: string;
  contact_number: string;
  system_version: string;
}) => {
  const response = await axios.put(
    `${API}/system-settings`,
    data,
    authHeader()
  );

  return response.data;
};