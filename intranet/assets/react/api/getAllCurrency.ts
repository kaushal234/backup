import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllCurrencyResponse } from "../types/IGetAllCurrencyResponse";
import { handleError } from "../utils/utils";

export const getAllCurrency = async (): Promise<
  IApiResponse<IGetAllCurrencyResponse>
> => {
  try {
    const response = await client.get("/finance/currencies");
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
