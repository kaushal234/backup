import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPutContactCampaignResponse } from "../types/IPutContactCampaignResponse";

export interface IPutContactCampaignApiPayload {
  id: string;
  name: string;
  startedAt: string;
  endedAt: string;
  status: string;
  description: string;
  businessUnits?: Array<string>;
  contacts?: Array<string>;
}

export const PutContactCampaign = async (
  data: IPutContactCampaignApiPayload
): Promise<IApiResponse<IPutContactCampaignResponse>> => {
  try {
    const response = await client.put(`/contact_campaigns/${data.id}`, data);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
