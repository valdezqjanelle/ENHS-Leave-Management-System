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
export const downloadLeavePdf = async (leaveId: number): Promise<Blob> => {
  const response = await api.get(
    `/leave-applications/${leaveId}/pdf`,
    {
      responseType: "blob",
      headers: {
        Accept: "application/pdf",
      },
    }
  );

  // Make sure Laravel actually returned a PDF.
  const contentType =
    response.headers["content-type"] ||
    response.headers["Content-Type"] ||
    "";

  console.log("PDF response:", {
    status: response.status,
    contentType,
    size: response.data?.size,
    type: response.data?.type,
  });

  if (!response.data || response.data.size === 0) {
    throw new Error("The server returned an empty PDF.");
  }

  // If Laravel returned an HTML error page, don't try to display it as PDF.
  if (
    contentType.includes("text/html") ||
    response.data.type === "text/html"
  ) {
    const text = await response.data.text();

    console.error("Server returned HTML instead of PDF:", text);

    throw new Error(
      "The server returned HTML instead of a PDF."
    );
  }

  // Force the correct MIME type.
  return new Blob(
    [response.data],
    { type: "application/pdf" }
  );
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