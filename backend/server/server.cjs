const express = require("express");
const cors = require("cors");

const app = express();

app.use(cors({
    origin: "https://enhs-leave-management-system-woad.vercel.app"
}));

app.use(express.json());

const LARAVEL_API = "https://enhs-leave-management-system.onrender.com";

app.get("/api/test", async (req, res) => {
    try {
        const response = await fetch(
            `${LARAVEL_API}/api/leave-applications/deleted`,
            {
                headers: {
                    Authorization: req.headers.authorization || "",
                    Accept: "application/json",
                },
            }
        );

        const data = await response.text();

        res.status(response.status).send(data);

    } catch (error) {
        console.error(error);

        res.status(500).json({
            message: "Proxy request failed",
            error: error.message
        });
    }
});

app.listen(3000, () => {
    console.log("CORS proxy running on port 3000");
});