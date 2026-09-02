import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllContractSubCategoriesResponse } from "../types/IGetAllContractSubCategoriesResponse";
import { handleError } from "../utils/utils";

export const getAllContractSubCategories = async (): Promise<
  IApiResponse<IGetAllContractSubCategoriesResponse>
> => {
  try {
    const response = await client.get("/contract/sub_categories");
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
