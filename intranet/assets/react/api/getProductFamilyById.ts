import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IGetProductFamilyByIdResponse } from "../types/IGetProductFamilyByIdResponse";

interface IGetProductFamilyByIdApiPayload {
  id: string;
}

export const getProductFamilyById = async (
  data: IGetProductFamilyByIdApiPayload
): Promise<IApiResponse<IGetProductFamilyByIdResponse>> => {
  try {
    const response = await client.get(`/sales/product_families/${data.id}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
