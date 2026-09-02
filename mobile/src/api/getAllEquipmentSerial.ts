import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllEquipmentSerialResponse } from "../@type/IGetAllEquipmentSerialResponse";

interface IGetAllEquipmentSerialApiPayload {
  serialNumber: string;
}

interface IGetAllEquipmentSerialApiParams {
  "equipmentRecord.serialNumber"?: string;
  schematics: "";
}

const DEFAULT_QUERY_PARAMS: IGetAllEquipmentSerialApiParams = {
  schematics: "",
};

export const getAllEquipmentSerial = async (
  data: IGetAllEquipmentSerialApiPayload
) => {
  try {
    const queryParams: IGetAllEquipmentSerialApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams["equipmentRecord.serialNumber"] = data.serialNumber;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });

    const url = `${process.env.REACT_APP_API_BASE_URL}/equipment_serials?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllEquipmentSerialResponse>;
  } catch (error) {
    return handleApiError<IGetAllEquipmentSerialResponse>(error);
  }
};
