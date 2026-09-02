import { AxiosResponse } from "axios";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostTechnicianOnCallApiResponse } from "../@type/IPostTechnicalOnCallApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";
import {
  IPostCommentByTocIdApiPayload,
  postCommentById,
} from "./postCommentById";

export interface IPostTechnicianOnCallApiPayload {
  originalTitle: string;
  originalDescription: string;
  status?: string;
  equipmentRecord: string | null;
  assignee?: string;
  technician?: string;
  errorCodes?: string;
  unitOperationalStatus?: string;
  technicianOnCallType: string;
  serviceActivity: string;
  indiceFactor: string;
  originalSymptoms?: string;
  originalRootCause?: string;
  originalSolution?: string;
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
  thirdPartyRef?: string | null;
  serialNumber?: string;
  confidential?: boolean;
  reason?: string;
}

export const postTechnicianOnCall = async (
  data: IPostTechnicianOnCallApiPayload
) => {
  try {
    const { reason, ...rest } = data;

    const URL = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_calls`;
    const response: AxiosResponse<IPostTechnicianOnCallApiResponse> =
      await http.post(URL, rest);

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
