import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllProductFamilyTagsResponse } from "../types/IGetAllProductFamilyTagsResponse";
import { handleError } from "../utils/utils";

export const getAllProductFamilyTags = async (): Promise<
  IApiResponse<IGetAllProductFamilyTagsResponse>
> => {
  try {
    const response = await client.get("/sales/product_family_tags");
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
