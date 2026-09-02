import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostTocSparePartsRequestApiResponse } from "../@type/IPostTocSparePartsRequestApiResponse";
import { IDeliveryAddress, IPart } from "../@type/ITocSprFormData";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IPostTocSparePartsRequestApiPayload {
  tocId: number;
  deliveryAddress: string | IDeliveryAddress;
  customer: string;
  sso: string;
  factory: string;
  erpLocation: string;
  sph: string;
  equipmentRecords: Array<string>;
  customers: Array<string>;
  airport: string;
  activity: string;
  type: string;
  technicianOnCall: string;
  deliveryNotes: string;
  parts: Array<IPart>;
}

export const postTocSparePartsRequest = async (
  data: IPostTocSparePartsRequestApiPayload
): Promise<IBasicApiResponse<IPostTocSparePartsRequestApiResponse>> => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/parts/toc_spare_parts_requests`;
    const response = await http.post(URL, data);
    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleApiError<IPostTocSparePartsRequestApiResponse>(error);
  }
};
