import api from "./api";

// =======================
// LEAVE TYPES
// =======================
export const getLeaveTypes = async () => {
  const { data } = await api.get("/leave-types");
  return data;
};

// =======================
// SUBMIT LEAVE (Employee)
// =======================
export const submitLeave = async (data: FormData) => {
    const response = await api.post(
        "/leave-applications",
        data
    );

    return response.data;
};

// =======================
// EMPLOYEE
// =======================
export const getMyLeaves = async () => {
  const { data } = await api.get("/my-leave-applications");
  return data;
};

export const getMyLeave = async (id: number) => {
  const { data } = await api.get(`/my-leave-applications/${id}`);
  return data;
};

// =======================
// ADMIN
// =======================
export const getLeave = async (id: number) => {
  const { data } = await api.get(`/leave-applications/${id}`);
  return data;
};
