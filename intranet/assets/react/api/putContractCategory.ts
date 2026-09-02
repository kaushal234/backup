import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPutContractCategoryResponse } from "../types/IPutContractCategoryResponse";

export type IPutContractCategoryApiPayload = {
  id: string;
  data: Partial<ICategory>;
};

interface ICategory {
  displayedName: string;
}

export const putContractCategory = async (
  data: IPutContractCategoryApiPayload
): Promise<IApiResponse<IPutContractCategoryResponse>> => {
  try {
    const response = await client.put(
      `/contract/categories/${data.id}`,
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
