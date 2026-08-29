import axios from "axios";

const API = "https://enhs-leave-management-system.onrender.com/api";

const authHeader = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

// Get all non-teaching personnel records
export const getNonTeachingPersonnelRecords = async () => {
  const response = await axios.get(
    `${API}/non-teaching-personnel-records`,
    authHeader()
  );

  return response.data;
};

// Get one non-teaching personnel record
export const getNonTeachingPersonnelRecord = async (id: number) => {
  const response = await axios.get(
    `${API}/non-teaching-personnel-records/${id}`,
    authHeader()
  );

  return response.data;
};

// Create non-teaching personnel record
export const createNonTeachingPersonnelRecord = async (data: any) => {
  const response = await axios.post(
    `${API}/non-teaching-personnel-records`,
    data,
    authHeader()
  );

  return response.data;
};

// Update non-teaching personnel record
export const updateNonTeachingPersonnelRecord = async (
  id: number,
  data: any
) => {
  const response = await axios.put(
    `${API}/non-teaching-personnel-records/${id}`,
    data,
    authHeader()
  );

  return response.data;
};

// Delete non-teaching personnel record
export const deleteNonTeachingPersonnelRecord = async (id: number) => {
  const response = await axios.delete(
    `${API}/non-teaching-personnel-records/${id}`,
    authHeader()
  );

  return response.data;
};