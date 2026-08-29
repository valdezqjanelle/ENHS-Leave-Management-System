const express = require("express");
const cors = require("cors");

const app = express();

app.use(cors({
    origin: "https://enhs-leave-management-system-woad.vercel.app"
}));

app.use(express.json());

app.get("/api/test", (req, res) => {
    res.json({
        message: "CORS test is working!"
    });
});

app.listen(3000, () => {
    console.log("CORS test server running on port 3000");
});