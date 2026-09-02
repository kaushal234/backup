import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";
import { IGetContractCategoryByIdResponse } from "../types/IGetContractCategoryByIdResponse";

interface IGetContractCategoryByIdApiPayload {
  id: string;
}

export const getContractCategoryById = async (
  data: IGetContractCategoryByIdApiPayload
): Promise<IApiResponse<IGetContractCategoryByIdResponse>> => {
  try {
    const response = await client.get(`/contract/categories/${data.id}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
