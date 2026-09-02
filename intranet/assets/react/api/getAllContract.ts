import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllContractResponse } from "../types/IGetAllContractResponse";
import { handleError } from "../utils/utils";

export const getAllContract = async (): Promise<
  IApiResponse<IGetAllContractResponse>
> => {
  try {
    const response = await client.get("/contracts");
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
