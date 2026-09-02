import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";
import { IGetContractSubCategoryByIdResponse } from "../types/IGetContractSubCategoryByIdResponse";

interface IGetContractSubCategoryByIdApiPayload {
  id: string;
}

export const getContractSubCategoryById = async (
  data: IGetContractSubCategoryByIdApiPayload
): Promise<IApiResponse<IGetContractSubCategoryByIdResponse>> => {
  try {
    const response = await client.get(`/contract/sub_categories/${data.id}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
