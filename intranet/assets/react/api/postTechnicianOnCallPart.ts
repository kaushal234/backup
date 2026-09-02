import { client } from "../store";

export const postTechnicianOnCallPart = async ({ values }: any) => {
  try {
    return await client.post("/service/technician_on_call_parts", values);
  } catch (error) {
    console.error(error);
    return error;
  }
};
