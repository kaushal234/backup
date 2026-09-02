import { IGetAuditLogResponse } from "../@type/IGetAuditLogResponse";
import { handleApiError } from "../utils/api";
import { getAuditLog } from "./getAuditLog";

interface IGetNmcTimeByIdApiPayload {
  tocId: string;
}

export const getNmcTimeById = async (data: IGetNmcTimeByIdApiPayload) => {
  try {
    return getAuditLog({
      auditType: "technician_on_call",
      property: "unitOperationalStatus",
      referenceId: data.tocId,
    });
  } catch (error) {
    return handleApiError<IGetAuditLogResponse>(error);
  }
};
