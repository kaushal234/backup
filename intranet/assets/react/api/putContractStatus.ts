import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPutContractStatusResponse } from "../types/IPutContractStatusResponse";

export type IPutContractStatusApiPayload = {
  id: string;
  data: Partial<IContractStatus>;
};

interface IContractStatus {
  observationStatus: string;
  status: string;
}

export const putContractStatus = async (
  data: IPutContractStatusApiPayload
): Promise<IApiResponse<IPutContractStatusResponse>> => {
  try {
    const response = await client.put(
      `/contracts/${data.id}/status`,
      data.data
    );
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
