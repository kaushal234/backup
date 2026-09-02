import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPostProductFamilyResponse } from "../types/IPostProductFamilyResponse";

export interface IPostProductFamilyApiPayload {
  name: string;
  productType: string;
  tags: Array<string>;
  manufacturingFactories: Array<string>;
  publicForTLD: boolean;
  publicForAerospecialties: boolean;
  publicForSAS: boolean;
  hidden: boolean;
  englishDescription: string | null;
  frenchDescription: string | null;
  spanishDescription: string | null;
  portugueseDescription: string | null;
  chineseDescription: string | null;
  japaneseDescription: string | null;
  germanDescription: string | null;
  russianDescription: string | null;
}

export const postProductFamily = async (
  data: IPostProductFamilyApiPayload
): Promise<IApiResponse<IPostProductFamilyResponse>> => {
  try {
    const response = await client.post("/sales/product_families", data);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
