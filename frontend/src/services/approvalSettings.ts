import axios from "axios";

const API = "http://127.0.0.1:8000/api";

const authHeader = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

export const getApprovalSettings = async () => {
  const response = await axios.get(
    `${API}/approval-settings`,
    authHeader()
  );

  return response.data;
};

export const updateApprovalSettings = async (data: {
  require_admin_review: boolean;
  require_principal_endorsement: boolean;
  allow_admin_remarks: boolean;
  auto_update_status: boolean;
  auto_update_balance: boolean;
}) => {
  const response = await axios.put(
    `${API}/approval-settings`,
    data,
    authHeader()
  );

  return response.data;
};