import { GoogleGenAI, Type } from "@google/genai";
import dotenv from "dotenv";

dotenv.config();

const ai = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY });

export async function classifyMaintenanceFault(imageBuffer, mimeType, descriptionText) {
  const response = await ai.models.generateContent({
    model: "gemini-2.5-flash",
    config: {
      systemInstruction: `You are an automated campus maintenance classifier. 
      Analyze the provided image and description to categorize the defect into one of three strict priority levels: 
      CRITICAL (immediate dangerous hazards), 
      MODERATE (halts classes/activities), 
      or MINOR (cosmetic or easily bypassed).`,
      responseMimeType: "application/json",
      responseSchema: {
        type: Type.OBJECT,
        properties: {
          priority: {
            type: Type.STRING,
            enum: ["CRITICAL", "MODERATE", "MINOR"],
            description: "The priority tag of the maintenance issue.",
          },
        },
        required: ["priority"],
      },
    },
    contents: [
      {
        inlineData: {
          data: imageBuffer.toString("base64"),
          mimeType: mimeType,
        },
      },
      {
        text: `Report Description: ${descriptionText}`,
      },
    ],
  });

  const result = JSON.parse(response.text);
  return result.priority;
}