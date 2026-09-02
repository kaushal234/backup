import axios from "axios";
import https from "https";
import { IError } from "../@types/IError.ts";

const env = process.env.APP_ENV || "dev";

// check if the user has a valid API token
export const isAuthenticated = async (token: string) => {
  const httpClientConfig = {
    baseURL: process.env.API_URI,
    headers: {
      Accept: "application/ld+json",
      "Content-type": "application/ld+json",
      Authorization: `Bearer ${token}`,
    },
    httpsAgent: new https.Agent({ rejectUnauthorized: env === "prod" }), // ignore ssl certificate issue on dev/test env
  };
  const client = axios.create(httpClientConfig);

  try {
    const response = await client.get("/me");
    console.info(
      "Response status code and user when calling internal API :",
      response.status,
      response.data.email,
      env
    );

    return !!(response.data && response.status === 200);
  } catch (error) {
    const err = error as IError;
    console.error("Error when calling internal API :", err.message);
    return false;
  }
};
