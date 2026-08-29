import axios from "axios";

const API = "https://enhs-leave-management-system.onrender.com/api";

const authHeader = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

// Get all teaching personnel records
export const getTeachingPersonnelRecords = async () => {
  const response = await axios.get(
    `${API}/teaching-personnel-records`,
    authHeader()
  );

  return response.data;
};

// Get one teaching personnel record
export const getTeachingPersonnelRecord = async (id: number) => {
  const response = await axios.get(
    `${API}/teaching-personnel-records/${id}`,
    authHeader()
  );

  return response.data;
};

// Create teaching personnel record
export const createTeachingPersonnelRecord = async (data: any) => {
  const response = await axios.post(
    `${API}/teaching-personnel-records`,
    data,
    authHeader()
  );

  return response.data;
};

// Update teaching personnel record
export const updateTeachingPersonnelRecord = async (
  id: number,
  data: any
) => {
  const response = await axios.put(
    `${API}/teaching-personnel-records/${id}`,
    data,
    authHeader()
  );

  return response.data;
};

// Delete teaching personnel record
export const deleteTeachingPersonnelRecord = async (id: number) => {
  const response = await axios.delete(
    `${API}/teaching-personnel-records/${id}`,
    authHeader()
  );

  return response.data;
};