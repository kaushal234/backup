import qs from "qs";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IGetAuditLogResponse } from "../@type/IGetAuditLogResponse";

interface IGetAuditLogApiPayload {
  auditType: string;
  property: string;
  referenceId: string;
}

export type IGetAuditLogFullResponse = IBasicApiResponse<IGetAuditLogResponse>;

export const getAuditLog = async (data: IGetAuditLogApiPayload) => {
  try {
    const queryParams: IGetAuditLogApiPayload = data;
    const queryString = qs.stringify(queryParams);
    const url = `${process.env.REACT_APP_API_BASE_URL}/audit_logs/time_by_reference?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAuditLogResponse>;
  } catch (error) {
    return handleApiError<IGetAuditLogResponse>(error);
  }
};
