import axios from "axios";

const API = "http://127.0.0.1:8000/api";

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