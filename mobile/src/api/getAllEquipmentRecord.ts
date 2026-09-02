import qs from "qs";
import { handleApiError, handlePaginationQueryParams } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPagination } from "../@type/IPagination";
import { ISortByIdApiParams } from "../@type/ISortByIdApiParams";
import { IGetAllEquipmentRecordResponse } from "../@type/IGetAllEquipmentRecordResponse";
import { IGetAllEquipmentRecordFilters } from "../@type/IGetAllEquipmentRecordFilters";
import { IGetAllEquipmentRecordApiSortPayload } from "../@type/IGetAllEquipmentRecordApiSortPayload";
import { ISortOption } from "../@type/ISortOption";

interface IGetAllEquipmentRecordApiPayload
  extends IPagination,
    IGetAllEquipmentRecordApiSortPayload,
    IGetAllEquipmentRecordFilters {}

interface IGetAllEquipmentRecordApiParams
  extends IPagination,
    ISortByIdApiParams,
    IGetAllEquipmentRecordFilters {
  "order[serialNumber]"?: ISortOption;
  normalization_groups: Array<"iata_code_detail">;
}

const DEFAULT_QUERY_PARAMS: IGetAllEquipmentRecordApiParams = {
  normalization_groups: ["iata_code_detail"],
};

export const getAllEquipmentRecord = async (
  data: IGetAllEquipmentRecordApiPayload
) => {
  try {
    let queryParams: IGetAllEquipmentRecordApiParams = DEFAULT_QUERY_PARAMS;
    queryParams = handlePaginationQueryParams(data, queryParams);
    queryParams["order[serialNumber]"] = data.sortBySerialNumber;
    // filters
    queryParams.serialNumber = data.serialNumber;
    queryParams["product.family.productType"] =
      data["product.family.productType"];
    queryParams.product = data.product;
    queryParams.airport = data.airport;
    queryParams.buyer = data.buyer;
    queryParams.endUser = data.endUser;
    queryParams.maintainer = data.maintainer;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/equipment_records?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllEquipmentRecordResponse>;
  } catch (error) {
    return handleApiError<IGetAllEquipmentRecordResponse>(error);
  }
};
