import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetEquipmentRecordResponse } from "../@type/IGetEquipmentRecordResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

interface IGetEquipmentRecordByIdApiPayload {
  id: string;
}

export const getEquipmentRecordById = async (
  data: IGetEquipmentRecordByIdApiPayload
) => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/equipment_records/${data.id}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetEquipmentRecordResponse>;
  } catch (error) {
    return handleApiError<IGetEquipmentRecordResponse>(error);
  }
};
