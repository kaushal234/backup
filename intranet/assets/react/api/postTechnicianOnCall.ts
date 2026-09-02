import { AxiosResponse } from "axios";
import { handleApiError } from "../utils/api";
import { client } from "../store";
import {
  IPostCommentByTocIdApiPayload,
  postCommentById,
} from "./postCommentById";
import { IPostTechnicianOnCallApiResponse } from "../types/IPostTechnicalOnCallApiResponse";
import { IBasicApiResponse } from "../types/IBasicApiResponse";

export interface IPostTechnicianOnCallApiPayload {
  originalTitle: string;
  originalDescription: string;
  status?: string;
  equipmentRecord: string | null;
  assignee?: string;
  technician: string;
  errorCodes?: string;
  unitOperationalStatus?: string;
  technicianOnCallType: string;
  serviceActivity: string;
  indiceFactor: string;
  symptoms?: string;
  rootCause?: string;
  solution?: string;
  airport: string;
  salesOrganisationService: string;
  nestedCustomerServiceRecord?: {
    leader: string | null;
    plannedAt: string | null;
  };
  tags?: Array<string>;
  mainContact?: string;
  contacts?: Array<string>;
  hourMeter?: number;
  customer: string;
  thirdPartyName?: string | null;
  serialNumber?: string;
  confidential?: boolean;
  reason?: string;
}

export const postTechnicianOnCall = async (
  data: IPostTechnicianOnCallApiPayload
) => {
  try {
    const { reason, ...rest } = data;

    const response: AxiosResponse<IPostTechnicianOnCallApiResponse> =
      await client.post(`/service/technician_on_calls`, rest);

    if (response.data["@id"] && data.confidential && reason) {
      const params: IPostCommentByTocIdApiPayload = {
        "@id": response.data["@id"],
        comment: reason,
        public: false,
        metadata: {
          notifications: false,
          confidential: data.confidential,
        },
      };
      await postCommentById(params);
    }

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostTechnicianOnCallApiResponse>;
  } catch (error) {
    return handleApiError<IPostTechnicianOnCallApiResponse>(error);
  }
};
