import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IGetContractByIdResponse } from "../types/IGetContractByIdResponse";

interface IGetContractByIdApiPayload {
  id: string;
}

export const getContractById = async (
  data: IGetContractByIdApiPayload
): Promise<IApiResponse<IGetContractByIdResponse>> => {
  try {
    const response = await client.get(`/contracts/${data.id}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
