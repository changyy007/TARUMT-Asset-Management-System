import express from "express";
import multer from "multer"; // for handling file uploads
import { classifyMaintenanceFault } from "./classifier.js";

const app = express();
const upload = multer(); // store upload in memory buffer

app.post("/api/tickets", upload.single("image"), async (req, res) => {
  try {
    const imageBuffer = req.file.buffer;
    const mimeType = req.file.mimetype;
    const description = req.body.description;

    // 1. Get priority from Gemini
    const priority = await classifyMaintenanceFault(imageBuffer, mimeType, description);

    // 2. Save ticket to Database with the returned priority ("CRITICAL", "MODERATE", or "MINOR")
    // await db.tickets.create({ description, priority, location: req.body.location });

    res.json({ success: true, priority });
  } catch (error) {
    res.status(500).json({ error: "Failed to classify fault." });
  }
});