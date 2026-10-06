import axios from "axios";

const API = "https://enhs-leave-management-system.onrender.com/api";

const authConfig = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

export const getAuthorizedSignatory = async () => {
  const response = await axios.get(
    `${API}/admin/authorized-signatory`,
    authConfig(),
  );

  return response.data;
};

export const getAuthorizedSignatorySignature = async () => {
  const response = await axios.get(
    `${API}/admin/authorized-signatory/signature`,
    {
      ...authConfig(),
      responseType: "blob",
    },
  );

  return response.data as Blob;
};

export const saveAuthorizedSignatory = async (data: FormData) => {
  const response = await axios.post(
    `${API}/admin/authorized-signatory`,
    data,
    authConfig(),
  );

  return response.data;
};
