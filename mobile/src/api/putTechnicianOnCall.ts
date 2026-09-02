import { AxiosResponse } from "axios";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostTechnicianOnCallApiResponse } from "../@type/IPostTechnicalOnCallApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";
import {
  IPostCommentByTocIdApiPayload,
  postCommentById,
} from "./postCommentById";

export type IPutTechnicianOnCallApiPayload = {
  id: string;
  data: Partial<ITechnicianOnCall>;
};

interface ITechnicianOnCall {
  originalTitle: string;
  originalDescription: string;
  status: string;
  equipmentRecord: string | null;
  assignee: string;
  technician?: string;
  errorCodes: string;
  unitOperationalStatus: string;
  technicianOnCallType: string;
  serviceActivity: string;
  indiceFactor: string;
  originalSymptoms: string;
  originalRootCause: string;
  originalSolution: string;
  airport: string;
  salesOrganisationService: string;
  tags: Array<string>;
  nestedCustomerServiceRecord?: {
    leader: string | null;
    plannedAt: string | null;
  };
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

export const putTechnicianOnCall = async (
  data: IPutTechnicianOnCallApiPayload
) => {
  try {
    const { reason, ...rest } = data.data;
    const URL = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_calls/${data.id}`;
    const response: AxiosResponse<IPostTechnicianOnCallApiResponse> =
      await http.put(URL, rest);

    if (response.data["@id"] && reason) {
      const params: IPostCommentByTocIdApiPayload = {
        "@id": response.data["@id"],
        comment: reason,
        public: false,
        metadata: {
          notifications: false,
          confidential: data.data.confidential,
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
