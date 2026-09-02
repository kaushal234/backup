import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPutContactCampaignResponse } from "../types/IPutContactCampaignResponse";

interface IGetContactCampaignByIdApiPayload {
  id: string;
}

export const getContactCampaignById = async (
  data: IGetContactCampaignByIdApiPayload
): Promise<IApiResponse<IPutContactCampaignResponse>> => {
  try {
    const response = await client.get(`/contact_campaigns/${data.id}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
