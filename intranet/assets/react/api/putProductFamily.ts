import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPutProductFamilyResponse } from "../types/IPutProductFamilyResponse";

export type IPutProductFamilyApiPayload = {
  id: string;
  data: Partial<IProductFamily>;
};

interface IProductFamily {
  id: string;
  name: string;
  productType: string;
  tags: Array<string>;
  manufacturingFactories: Array<string>;
  publicForTLD: boolean;
  publicForAerospecialties: boolean;
  publicForSAS: boolean;
  hidden: boolean;
  englishDescription?: string | null;
  frenchDescription?: string | null;
  spanishDescription?: string | null;
  portugueseDescription?: string | null;
  chineseDescription?: string | null;
  japaneseDescription?: string | null;
  germanDescription?: string | null;
  russianDescription?: string | null;
}

export const putProductFamily = async (
  data: IPutProductFamilyApiPayload
): Promise<IApiResponse<IPutProductFamilyResponse>> => {
  try {
    const response = await client.put(
      `/sales/product_families/${data.id}`,
      data.data
    );
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
