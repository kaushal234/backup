import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPutContractSubCategoryResponse } from "../types/IPutContractSubCategoryResponse";

export type IPutContractSubCategoryApiPayload = {
  id: string;
  data: Partial<ISubCategory>;
};

interface ISubCategory {
  displayedName: string;
}

export const putContractSubCategory = async (
  data: IPutContractSubCategoryApiPayload
): Promise<IApiResponse<IPutContractSubCategoryResponse>> => {
  try {
    const response = await client.put(
      `/contract/sub_categories/${data.id}`,
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
