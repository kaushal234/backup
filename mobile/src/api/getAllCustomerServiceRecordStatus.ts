import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllCustomerServiceRecordStatusResponse } from "../@type/IGetAllCustomerServiceRecordStatusResponse";

export const getAllCustomerServiceRecordStatus = async () => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/places/service/default_customer_service_records`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllCustomerServiceRecordStatusResponse>;
  } catch (error) {
    return handleApiError<IGetAllCustomerServiceRecordStatusResponse>(error);
  }
};
