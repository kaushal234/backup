import qs from "qs";
import { client } from "../store";
import { handleApiError } from "../utils/api";
import { handlePaginationQueryParams } from "../utils/utils";
import { IBasicApiResponse } from "../types/IBasicApiResponse";
import { IPagination } from "../types/IPagination";
import { ISortByIdApiParams } from "../types/ISortByIdApiParams";
import { IGetAllEquipmentRecordResponse } from "../types/IGetAllEquipmentRecordResponse";
import { ISortOption } from "../types/ISortOption";
import { IGetAllEquipmentRecordFilters } from "../types/IGetAllEquipmentRecordFilters";
import { IGetAllEquipmentRecordApiSortPayload } from "../types/IGetAllEquipmentRecordApiSortPayload";

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
    const response = await client.get(`/equipment_records?${queryString}`);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllEquipmentRecordResponse>;
  } catch (error) {
    return handleApiError<IGetAllEquipmentRecordResponse>(error);
  }
};
