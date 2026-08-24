import api from "./api";

export const createDatabaseBackup = () => {
  return api.post("/backups");
};

export const getBackups = () => {
  return api.get("/backups");
};

export const downloadBackup = (id: number) => {
  return api.get(`/backups/${id}/download`, {
    responseType: "blob",
  });
};