import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IGetCustomerServiceRecordFileByIdApiPayload {
  csrId: string;
  fileId: string;
}

export interface IGetCustomerServiceRecordFileByIdApiResponse {
  blob?: Blob;
  url?: string;
}

export const getCustomerServiceRecordFileById = async (
  data: IGetCustomerServiceRecordFileByIdApiPayload
) => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/service/customer_service_records/${data.csrId}/files/${data.fileId}`;
    const response = await http.get(url, { isBlob: true });
    return {
      status: response.status,
      data: {
        blob: response.data,
        url: response.data && URL.createObjectURL(response.data),
      },
    } as IBasicApiResponse<IGetCustomerServiceRecordFileByIdApiResponse>;
  } catch (error) {
    return handleApiError<IGetCustomerServiceRecordFileByIdApiResponse>(error);
  }
};
