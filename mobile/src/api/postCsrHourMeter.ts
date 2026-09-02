import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostCsrHourMeterApiResponse } from "../@type/IPostCsrHourMeterApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IPostCsrHourMeterApiPayload {
  hourMeter: number;
  equipmentRecordId: number;
  customerServiceRecordIri: string;
}

export interface IPostCsrHourMeterApiPayloadFinal {
  hourMeter: number;
  equipmentRecord: string;
  customerServiceRecord: string;
}
export const postCsrHourMeter = async (data: IPostCsrHourMeterApiPayload) => {
  try {
    const payload: IPostCsrHourMeterApiPayloadFinal = {
      hourMeter: data.hourMeter,
      equipmentRecord: `/equipment_records/${data.equipmentRecordId}`,
      customerServiceRecord: data.customerServiceRecordIri,
    };
    const URL = `${process.env.REACT_APP_API_BASE_URL}/support/equipment_record/customer_service_record_hour_meter_transactions`;
    const response = await http.post(URL, payload);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostCsrHourMeterApiResponse>;
  } catch (error) {
    return handleApiError<IPostCsrHourMeterApiResponse>(error);
  }
};
