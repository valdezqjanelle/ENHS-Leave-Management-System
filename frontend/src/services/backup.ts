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

export const restoreDatabaseBackup = (file: File) => {
  const formData = new FormData();

  formData.append("backup_file", file);

  return api.post("/backups/restore", formData);
};