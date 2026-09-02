import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPostContractResponse } from "../types/IPostContractResponse";

export interface IPostContractApiPayload {
  shortDescription: string;
  description: string;
  startDate: string;
  expirationDate?: string | null;
  indefinitePeriodType?: boolean;
  automaticRenewal?: boolean;
  confidential?: boolean;
  observationTerm?: string | null;
  observationValue?: string | null;
  renewalPeriod?: number | null;
  renewalUnit?: string | null;
  externalParty?: string;
  internalParty?: Array<string>;
  otherPartySignatories?: Array<string>;
  jurisdiction?: string;
  value?: number | null;
  currency?: string;
  parentContract?: string;
  subCategory: string;
  divisions?: Array<string>;
  regions?: Array<string>;
  premises?: Array<string>;
  businessUnits?: Array<string>;
  customers?: Array<string>;
  comment?: string | null;
}

export const postContract = async (
  data: IPostContractApiPayload
): Promise<IApiResponse<IPostContractResponse>> => {
  try {
    const response = await client.post("/contracts", data);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
