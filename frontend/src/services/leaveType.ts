import axios from "axios";

const API = "https://enhs-leave-management-system.onrender.com/api";

const authHeader = () => ({
    headers:{
        Authorization:`Bearer ${localStorage.getItem("token")}`
    }
});


// GET ALL
export const getLeaveTypes = async()=>{

    const response = await axios.get(
        `${API}/leave-types`,
        authHeader()
    );

    return response.data;
};


// CREATE
export const createLeaveType = async(data:any)=>{

    const response = await axios.post(
        `${API}/leave-types`,
        data,
        authHeader()
    );

    return response.data;
};


// UPDATE
export const updateLeaveType = async(id:number,data:any)=>{

    const response = await axios.put(
        `${API}/leave-types/${id}`,
        data,
        authHeader()
    );

    return response.data;
};


// DELETE
export const deleteLeaveType = async(id:number)=>{

    const response = await axios.delete(
        `${API}/leave-types/${id}`,
        authHeader()
    );

    return response.data;
};