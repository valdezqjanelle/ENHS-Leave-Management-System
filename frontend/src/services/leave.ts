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
  try {
    const response = await api.post("/leave-applications", data);

    return response.data;
  } catch (error: any) {
    console.log("UPLOAD ERROR STATUS:", error.response?.status);
    console.log(
      "UPLOAD ERROR DATA:",
      JSON.stringify(error.response?.data, null, 2)
    );
    console.log("UPLOAD ERROR:", error);

    throw error;
  }
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

// =======================
// MY LEAVE BALANCE
// =======================
export const getMyLeaveBalance = async () => {
  const { data } = await api.get("/my-leave-balance");
  return data;
};

// =======================
// DDOWNLOAD LEAVE APPLICATION PDF
// =======================
export const downloadLeavePdf = async (leaveId: number) => {
  const response = await api.get(
    `/leave-applications/${leaveId}/pdf`,
    {
      responseType: "blob",
    }
  );

  return response.data;
};