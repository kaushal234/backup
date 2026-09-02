import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPostContactCampaignResponse } from "../types/IPostContactCampaignResponse";

export interface IPostContactCampaignApiPayload {
  name: string;
  startedAt: string;
  endedAt: string;
  status: string;
  description: string;
  businessUnits?: Array<string>;
  contacts?: Array<string>;
}

export const postContactCampaign = async (
  data: IPostContactCampaignApiPayload
): Promise<IApiResponse<IPostContactCampaignResponse>> => {
  try {
    const response = await client.post("/contact_campaigns", data);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
