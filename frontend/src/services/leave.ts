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
      headers: {
        Accept: "application/pdf",
      },
    }
  );

  return response.data;
};

 export const deleteLeaveApplication = async (leaveId: number) => {
  const response = await api.delete(
    `/leave-applications/${leaveId}`
  );
  return response.data;
};

export const restoreLeaveApplication = async (leaveId: number) => {
  const response = await api.post(
    `/leave-applications/${leaveId}/restore`
  );
  return response.data;
};

export const getDeletedLeaveApplications = async () => {
  const response = await api.get(
    `/leave-applications/deleted`
  );
  return response.data;
};

export const rejectLeaveApplication = async (
  leaveId: number,
  disapprovalReason?: string
) => {
  const response = await api.put(
    `/leave-applications/${leaveId}`,
    {
      final_status: "disapproved",
      disapproval_reason: disapprovalReason ?? "",
    }
  );

  return response.data;
};