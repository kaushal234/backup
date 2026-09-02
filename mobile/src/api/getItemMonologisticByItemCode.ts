import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetItemMonologisticResponse } from "../@type/IGetItemMonologisticResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

interface IGetItemMonologisticByItemCodeApiPayload {
  itemCode: string;
}

export const getItemMonologisticByItemCode = async (
  data: IGetItemMonologisticByItemCodeApiPayload
): Promise<IBasicApiResponse<IGetItemMonologisticResponse>> => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/ion/item_monologistics/${data.itemCode}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleApiError<IGetItemMonologisticResponse>(error);
  }
};
