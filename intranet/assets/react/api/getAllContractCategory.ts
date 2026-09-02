import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";
import { IGetAllContractCategoryResponse } from "../types/IGetAllContractCategoryResponse";

export const getAllContractCategory = async (): Promise<
  IApiResponse<IGetAllContractCategoryResponse>
> => {
  try {
    const response = await client.get(`/contract/categories`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
