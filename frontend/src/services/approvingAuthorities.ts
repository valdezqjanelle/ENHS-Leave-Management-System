import axios from "axios";

const API = "https://enhs-leave-management-system.onrender.com/api";

const authConfig = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

export interface AuthorityNameResponse {
  name: string;
}

export interface AuthorityNameSaveResponse {
  message?: string;
  data?: AuthorityNameResponse;
}

// 7.A Authorized Officer (name only)
export const getAuthorizedOfficer = async (): Promise<AuthorityNameResponse> => {
  const response = await axios.get(`${API}/admin/authorized-officer`, authConfig());

  return response.data;
};

export const saveAuthorizedOfficer = async (
  payload: AuthorityNameResponse,
): Promise<AuthorityNameSaveResponse> => {
  const response = await axios.put(
    `${API}/admin/authorized-officer`,
    payload,
    authConfig(),
  );

  return response.data;
};

// School Division Superintendent (name only)
export const getSchoolDivisionSuperintendent =
  async (): Promise<AuthorityNameResponse> => {
    const response = await axios.get(
      `${API}/admin/school-division-superintendent`,
      authConfig(),
    );

    return response.data;
  };

export const saveSchoolDivisionSuperintendent = async (
  payload: AuthorityNameResponse,
): Promise<AuthorityNameSaveResponse> => {
  const response = await axios.put(
    `${API}/admin/school-division-superintendent`,
    payload,
    authConfig(),
  );

  return response.data;
};
