import qs from "qs";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

interface IGetSchematicFileByIdApiPayload {
  site: string;
  product: string;
  date?: string;
}

interface IGetSchematicFileByIdApiParams {
  date?: string;
}

export interface IGetSchematicFileByIdApiResponse {
  blob?: Blob;
  url?: string;
}

export const getSchematicFileById = async (
  data: IGetSchematicFileByIdApiPayload
) => {
  try {
    const queryParams: IGetSchematicFileByIdApiParams = {};
    // filter
    queryParams.date = data.date;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });

    const url = `${process.env.REACT_APP_API_BASE_URL}/ion/bill-of-materials/drawings/site=${data.site};project=;product=${data.product}?${queryString}`;
    const response = await http.get(url, { isBlob: true });

    return {
      status: response.status,
      data: {
        blob: response.data,
        url: response.data && URL.createObjectURL(response.data),
      },
    } as IBasicApiResponse<IGetSchematicFileByIdApiResponse>;
  } catch (error) {
    return handleApiError<IGetSchematicFileByIdApiResponse>(error);
  }
};
