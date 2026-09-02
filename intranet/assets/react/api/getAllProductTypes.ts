import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllProductTypesResponse } from "../types/IGetAllProductTypesResponse";
import { handleError } from "../utils/utils";

export const getAllProductTypes = async (): Promise<
  IApiResponse<IGetAllProductTypesResponse>
> => {
  try {
    const response = await client.get("/sales/product_types");
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
