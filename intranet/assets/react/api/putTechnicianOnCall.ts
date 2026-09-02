import { AxiosResponse } from "axios";
import { handleApiError } from "../utils/api";
import { client } from "../store";
import {
  IPostCommentByTocIdApiPayload,
  postCommentById,
} from "./postCommentById";
import { IPostTechnicianOnCallApiResponse } from "../types/IPostTechnicalOnCallApiResponse";
import { IBasicApiResponse } from "../types/IBasicApiResponse";

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
  technician: string;
  errorCodes: string;
  unitOperationalStatus: string;
  technicianOnCallType: string;
  serviceActivity: string;
  indiceFactor: string;
  symptoms: string;
  rootCause: string;
  solution: string;
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
  serialNumber?: string;
  confidential?: boolean;
  reason?: string;
}

export const putTechnicianOnCall = async (
  data: IPutTechnicianOnCallApiPayload
) => {
  try {
    const { reason, ...rest } = data.data;

    const response: AxiosResponse<IPostTechnicianOnCallApiResponse> =
      await client.put(`/service/technician_on_calls/${data.id}`, rest);

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
