import qs from "qs";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllManufacturingFactoriesResponse } from "../types/IGetAllManufacturingFactoriesResponse";
import { handleError } from "../utils/utils";

interface IGetAllManufacturingFactoriesApiParams {
  "capability.factory": boolean;
  "state.hidden": boolean;
  "order[name]": "ASC";
}

const DEFAULT_QUERY_PARAMS: IGetAllManufacturingFactoriesApiParams = {
  "capability.factory": true,
  "state.hidden": false,
  "order[name]": "ASC",
};

export const getAllManufacturingFactories = async (): Promise<
  IApiResponse<IGetAllManufacturingFactoriesResponse>
> => {
  try {
    const queryString = qs.stringify(DEFAULT_QUERY_PARAMS, {
      arrayFormat: "brackets",
    });
    const response = await client.get(`/locations?${queryString}`);

    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
