import { client } from "../store";

export const deleteTechnicianOnCallPart = async ({ tocPartId }: any) => {
  try {
    return await client.delete(
      `/service/technician_on_call_parts/${tocPartId}`
    );
  } catch (error) {
    console.error(error);
    return error;
  }
};
