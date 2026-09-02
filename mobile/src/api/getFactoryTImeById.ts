import { IGetAuditLogResponse } from "../@type/IGetAuditLogResponse";
import { handleApiError } from "../utils/api";
import { getAuditLog } from "./getAuditLog";

interface IGetFactoryTimeByIdApiPayload {
  tocId: string;
}

export const getFactoryTimeById = async (
  data: IGetFactoryTimeByIdApiPayload
) => {
  try {
    return getAuditLog({
      auditType: "technician_on_call",
      property: "factoryFlag",
      referenceId: data.tocId,
    });
  } catch (error) {
    return handleApiError<IGetAuditLogResponse>(error);
  }
};
