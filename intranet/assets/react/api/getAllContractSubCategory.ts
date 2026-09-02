import qs from "qs";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllContractSubCategoryResponse } from "../types/IGetAllContractSubCategoryResponse";
import { handleError } from "../utils/utils";

interface IGetAllContractSubCategoryApiPayload {
  category?: string;
}

interface IGetAllContractSubCategoryApiParams {
  category?: string;
}

export const getAllContractSubCategory = async (
  data: IGetAllContractSubCategoryApiPayload
): Promise<IApiResponse<IGetAllContractSubCategoryResponse>> => {
  try {
    const queryParams: IGetAllContractSubCategoryApiParams = {};
    // filter
    queryParams.category = data.category;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(
      `/contract/sub_categories?${queryString}`
    );
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
