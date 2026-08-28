import axios from "axios";

const API = "http://127.0.0.1:8000/api";

const authHeader = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

// Get admin profile
export const getAdminProfile = async () => {
  const response = await axios.get(`${API}/admin/profile`, authHeader());

  return response.data;
};

export const getAdminPositions = async () => {
  const response = await axios.get(
    `${API}/admin/positions`,
    authHeader()
  );

  return response.data;
};

// Update admin profile
export const updateAdminProfile = async (data: {
  first_name: string;
  middle_name: string;
  last_name: string;
  sex: string;
  position: string;
  department: string;
  contact_number: string;
}) => {
  const response = await axios.put(
    `${API}/admin/profile`,
    data,
    authHeader()
  );

  return response.data;
};
// Update email
export const updateAdminEmail = async (email: string) => {
  const response = await axios.put(
    `${API}/admin/email`,
    {
      email,
    },
    authHeader(),
  );

  return response.data;
};

// Update password
export const updateAdminPassword = async (data: any) => {
  const response = await axios.put(`${API}/admin/password`, data, authHeader());

  return response.data;
};

// GET LEAVE TYPES
export const getLeaveTypes = async()=>{

    const response = await axios.get(
        `${API}/leave-types`,
        authHeader()
    );

    return response.data;

};



// CREATE LEAVE TYPE
export const createLeaveType = async(data:any)=>{

    const response = await axios.post(
        `${API}/leave-types`,
        data,
        authHeader()
    );

    return response.data;

};



// UPDATE LEAVE TYPE
export const updateLeaveType = async(
    id:number,
    data:any
)=>{

    const response = await axios.put(
        `${API}/leave-types/${id}`,
        data,
        authHeader()
    );


    return response.data;

};




// DELETE LEAVE TYPE
export const deleteLeaveType = async(id:number)=>{

    const response = await axios.delete(
        `${API}/leave-types/${id}`,
        authHeader()
    );


    return response.data;

};

