import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllCustomizedBillOfMaterialResponse } from "../@type/IGetAllCustomizedBillOfMaterialResponse";

interface IGetAllCustomizedBillOfMaterialApiPayload {
  site: string;
  project: string;
}

interface IGetAllCustomizedBillOfMaterialApiParams {
  depth: "20";
  flatResult: "1";
  itemsSignalCodeAttribute: "engineeringSignalCode";
  itemsSignalCodeFilter: "ESC|HSC|BSC|FLD";
  itemsSignalCodeFilterMethod: "Equals";
  productSignalCodeAttribute: "engineeringSignalCode";
  productSignalCodeFilter: "ESC|HSC|BSC|FLD";
  productSignalCodeFilterMethod: "Equals";
}

const DEFAULT_QUERY_PARAMS: IGetAllCustomizedBillOfMaterialApiParams = {
  depth: "20",
  flatResult: "1",
  itemsSignalCodeAttribute: "engineeringSignalCode",
  itemsSignalCodeFilter: "ESC|HSC|BSC|FLD",
  itemsSignalCodeFilterMethod: "Equals",
  productSignalCodeAttribute: "engineeringSignalCode",
  productSignalCodeFilter: "ESC|HSC|BSC|FLD",
  productSignalCodeFilterMethod: "Equals",
};

export const getAllCustomizedBillOfMaterial = async (
  data: IGetAllCustomizedBillOfMaterialApiPayload
) => {
  try {
    const queryParams: IGetAllCustomizedBillOfMaterialApiParams =
      DEFAULT_QUERY_PARAMS;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });

    const url = `${process.env.REACT_APP_API_BASE_URL}/ion/customized_bill_of_materials/site=${data.site};project=${data.project}?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllCustomizedBillOfMaterialResponse>;
  } catch (error) {
    return handleApiError<IGetAllCustomizedBillOfMaterialResponse>(error);
  }
};
