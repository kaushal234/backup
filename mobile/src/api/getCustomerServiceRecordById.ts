import qs from "qs";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetCustomerServiceRecordResponse } from "../@type/IGetCustomerServiceRecordResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

interface IGetCustomerServiceRecordByIdApiPayload {
  id: string;
}

interface IGetCustomerServiceRecordByIdApiParams {
  normalizationGroups: Array<"workflow">;
}

const DEFAULT_QUERY_PARAMS: IGetCustomerServiceRecordByIdApiParams = {
  normalizationGroups: ["workflow"],
};

export const getCustomerServiceRecordById = async (
  data: IGetCustomerServiceRecordByIdApiPayload
) => {
  try {
    const queryParams: IGetCustomerServiceRecordByIdApiParams =
      DEFAULT_QUERY_PARAMS;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/service/customer_service_records/${data.id}?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetCustomerServiceRecordResponse>;
  } catch (error) {
    return handleApiError<IGetCustomerServiceRecordResponse>(error);
  }
};
