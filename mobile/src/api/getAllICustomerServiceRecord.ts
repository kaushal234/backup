import qs from "qs";
import { handleApiError, handlePaginationQueryParams } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPagination } from "../@type/IPagination";
import { ISortOption } from "../@type/ISortOption";
import { SORT_OPTION } from "../constants/constants";
import { IGetAllCustomerServiceRecordFilters } from "../@type/IGetAllCustomerServiceRecordFilters";
import { IGetAllCustomerServiceRecordApiSortPayload } from "../@type/IGetAllCustomerServiceRecordApiSortPayload";
import { IGetAllCustomerServiceRequestResponse } from "../@type/IGetAllCustomerServiceRecordResponse";

interface IGetAllCustomerServiceRequestApiPayload
  extends IPagination,
    IGetAllCustomerServiceRecordFilters,
    IGetAllCustomerServiceRecordApiSortPayload {}

interface IGetAllCustomerServiceRequestApiParams
  extends IPagination,
    IGetAllCustomerServiceRecordFilters {
  "order[airport.code]"?: ISortOption;
  "order[createdAt]"?: ISortOption;
  "order[updatedAt]"?: ISortOption;
  "order[id]"?: ISortOption;
}

export const getAllCustomerServiceRecord = async (
  data: IGetAllCustomerServiceRequestApiPayload
) => {
  try {
    let queryParams: IGetAllCustomerServiceRequestApiParams = {};
    queryParams = handlePaginationQueryParams(data, queryParams);
    queryParams["order[airport.code]"] = data.sortByAirport;
    queryParams["order[createdAt]"] = data.sortByCreatedAt;
    queryParams["order[updatedAt]"] = data.sortByUpdatedAt;
    queryParams["order[id]"] = SORT_OPTION.ASC;
    // filter
    queryParams.equipmentRecord = data.equipmentRecord;
    queryParams.status = data.status;
    queryParams.createdBy = data.createdBy;
    queryParams["createdAt[after]"] = data["createdAt[after]"];
    queryParams["createdAt[before]"] = data["createdAt[before]"];
    queryParams["equipmentRecord.salesOrganisation"] =
      data["equipmentRecord.salesOrganisation"];
    queryParams["equipmentRecord.salesOrganisationService"] =
      data["equipmentRecord.salesOrganisationService"];
    queryParams["equipmentRecord.manufacturerLocation"] =
      data["equipmentRecord.manufacturerLocation"];
    queryParams["equipmentRecord.product.family.productType"] =
      data["equipmentRecord.product.family.productType"];
    queryParams["equipmentRecord.product"] = data["equipmentRecord.product"];
    queryParams.airport = data.airport;
    queryParams["interventions.leader"] = data["interventions.leader"];
    queryParams["equipmentRecord.endUser"] = data["equipmentRecord.endUser"];
    queryParams["completedAt[after]"] = data["completedAt[after]"];
    queryParams["completedAt[before]"] = data["completedAt[before]"];
    queryParams["closedAt[after]"] = data["closedAt[after]"];
    queryParams["closedAt[before]"] = data["closedAt[before]"];
    queryParams["airport.country"] = data["airport.country"];
    queryParams.discriminator = data.discriminator;

    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/service/customer_service_records?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllCustomerServiceRequestResponse>;
  } catch (error) {
    return handleApiError<IGetAllCustomerServiceRequestResponse>(error);
  }
};
