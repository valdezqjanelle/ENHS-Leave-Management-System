import axios from "axios";

const API =
  "https://enhs-leave-management-system.onrender.com/api";

const authHeader = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

// Get grade levels, sections, and subjects
export const getTeachingSetup = async () => {
  const response = await axios.get(
    `${API}/teaching-setup`,
    authHeader()
  );

  return response.data;
};

// Create grade level
export const createGradeLevel = async (data: any) => {
  const response = await axios.post(
    `${API}/teaching-setup/grade-levels`,
    data,
    authHeader()
  );

  return response.data;
};

// Update grade level
export const updateGradeLevel = async (
  id: number,
  data: any
) => {
  const response = await axios.put(
    `${API}/teaching-setup/grade-levels/${id}`,
    data,
    authHeader()
  );

  return response.data;
};

// Delete grade level
export const deleteGradeLevel = async (id: number) => {
  const response = await axios.delete(
    `${API}/teaching-setup/grade-levels/${id}`,
    authHeader()
  );

  return response.data;
};

// Create section
export const createSection = async (data: any) => {
  const response = await axios.post(
    `${API}/teaching-setup/sections`,
    data,
    authHeader()
  );

  return response.data;
};

// Update section
export const updateSection = async (
  id: number,
  data: any
) => {
  const response = await axios.put(
    `${API}/teaching-setup/sections/${id}`,
    data,
    authHeader()
  );

  return response.data;
};

// Delete section
export const deleteSection = async (id: number) => {
  const response = await axios.delete(
    `${API}/teaching-setup/sections/${id}`,
    authHeader()
  );

  return response.data;
};

// Create subject or specialization
export const createSubject = async (data: any) => {
  const response = await axios.post(
    `${API}/teaching-setup/subjects`,
    data,
    authHeader()
  );

  return response.data;
};

// Update subject or specialization
export const updateSubject = async (
  id: number,
  data: any
) => {
  const response = await axios.put(
    `${API}/teaching-setup/subjects/${id}`,
    data,
    authHeader()
  );

  return response.data;
};

// Delete subject or specialization
export const deleteSubject = async (id: number) => {
  const response = await axios.delete(
    `${API}/teaching-setup/subjects/${id}`,
    authHeader()
  );

  return response.data;
};