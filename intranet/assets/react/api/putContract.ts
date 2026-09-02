import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPutContractResponse } from "../types/IPutContractResponse";

export type IPutContractApiPayload = {
  id: string;
  data: Partial<IContract>;
};

interface IContract {
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
  owner?: string;
  status?: string;
  comment?: string | null;
}

export const putContract = async (
  data: IPutContractApiPayload
): Promise<IApiResponse<IPutContractResponse>> => {
  try {
    const response = await client.put(`/contracts/${data.id}`, data.data);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
