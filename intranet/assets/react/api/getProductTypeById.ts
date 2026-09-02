import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IProductType } from "../types/IGetAllProductTypesResponse";
import { handleError } from "../utils/utils";

interface IProductTypeId {
  id: string;
}
export const getProductTypeById = async ({
  id,
}: IProductTypeId): Promise<IApiResponse<IProductType>> => {
  try {
    const response = await client.get(`/sales/product_types/${id}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
