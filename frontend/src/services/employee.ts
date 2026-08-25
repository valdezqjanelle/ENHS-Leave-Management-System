import axios from "axios";

const API = "http://127.0.0.1:8000/api";

const authHeader = () => ({
    headers:{
        Authorization:`Bearer ${localStorage.getItem("token")}`
    }
});
// Get all employees
export const getEmployees = async () => {
  const response = await axios.get(
    `${API}/employees`,
    authHeader()
  );

  return response.data;
};

// Get all positions (for dropdown)
export const getPositions = async () => {
  const response = await axios.get(
    `${API}/positions`,
    authHeader()
  );

  return response.data;
};

// Create employee
export const createEmployee = async (data: any) => {
  const response = await axios.post(
    `${API}/employees`,
    data,
    authHeader()
  );

  return response.data;
};

// Update employee
export const updateEmployee = async (id: number, data: any) => {

  const response = await axios.put(
    `${API}/employees/${id}`,
    data,
    authHeader()
  );

  return response.data;
};

export const getMyProfile = async () => {  
    const response = await axios.get(`${API}/my-profile`, authHeader());
    return response.data;
}; 

// Update own profile
export const updateMyProfile = async (data: any) => {

    const response = await axios.put(
        `${API}/profile`,
        data,
        authHeader()
    );

    return response.data;
};


// Update email
export const updateEmail = async (email: string) => {

    const response = await axios.put(
        `${API}/profile/email`,
        {
            email
        },
        authHeader()
    );

    return response.data;
};


// Update password
export const updatePassword = async (data: any) => {

    const response = await axios.put(
        `${API}/profile/password`,
        data,
        authHeader()
    );

    return response.data;
};


// Update phone
export const updatePhone = async (contact_number: string) => {

    const response = await axios.put(
        `${API}/profile/phone`,
        {
            contact_number
        },
        authHeader()
    );

    return response.data;
};