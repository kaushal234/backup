import { client } from "../store";

export const putTechnicianOnCallPart = async ({ tocPartId, values }: any) => {
  try {
    return await client.put(
      `/service/technician_on_call_parts/${tocPartId}`,
      values
    );
  } catch (error) {
    console.error(error);
    return error;
  }
};
