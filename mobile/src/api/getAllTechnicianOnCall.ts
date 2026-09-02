import qs from "qs";
import { handleApiError, handlePaginationQueryParams } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPagination } from "../@type/IPagination";
import { IGetAllTechnicalOnCallsResponse } from "../@type/IGetAllTechnicalOnCallsResponse";
import { IGetAllTechnicalOnCallsFilters } from "../@type/IGetAllTechnicalOnCallsFilters";
import { IGetAllTechnicianOnCallApiSortPayload } from "../@type/IGetAllTechnicianOnCallApiSortPayload";
import { ISortOption } from "../@type/ISortOption";
import { SORT_OPTION } from "../constants/constants";

interface IGetAllTechnicianOnCallApiPayload
  extends IPagination,
    IGetAllTechnicalOnCallsFilters,
    IGetAllTechnicianOnCallApiSortPayload {}

interface IGetAllTechnicianOnCallApiParams
  extends IPagination,
    IGetAllTechnicalOnCallsFilters {
  "order[airport.code]"?: ISortOption;
  "order[indiceFactor]"?: ISortOption;
  "order[createdAt]"?: ISortOption;
  "order[updatedAt]"?: ISortOption;
  "order[id]"?: ISortOption;
}

export const getAllTechnicianOnCall = async (
  data: IGetAllTechnicianOnCallApiPayload
) => {
  try {
    let queryParams: IGetAllTechnicianOnCallApiParams = {};
    queryParams = handlePaginationQueryParams(data, queryParams);
    queryParams["order[airport.code]"] = data.sortByAirport;
    queryParams["order[indiceFactor]"] = data.sortByIFactor;
    queryParams["order[createdAt]"] = data.sortByCreatedAt;
    queryParams["order[updatedAt]"] = data.sortByUpdatedAt;
    queryParams["order[id]"] = SORT_OPTION.ASC;
    // filter
    queryParams.equipmentRecord = data.equipmentRecord;
    queryParams.status = data.status;
    queryParams.assignee = data.assignee;
    queryParams.unitOperationalStatus = data.unitOperationalStatus;
    queryParams.technicianOnCallType = data.technicianOnCallType;
    queryParams.serviceActivity = data.serviceActivity;
    queryParams.indiceFactor = data.indiceFactor;
    queryParams.tags = data.tags;
    queryParams.createdBy = data.createdBy;
    queryParams["createdAt[after]"] = data["createdAt[after]"];
    queryParams["createdAt[before]"] = data["createdAt[before]"];
    queryParams["solvedAt[after]"] = data["solvedAt[after]"];
    queryParams["solvedAt[before]"] = data["solvedAt[before]"];
    queryParams["equipmentRecord.salesOrganisation"] =
      data["equipmentRecord.salesOrganisation"];
    queryParams.salesOrganisationService = data.salesOrganisationService;
    queryParams["equipmentRecord.manufacturerLocation"] =
      data["equipmentRecord.manufacturerLocation"];
    queryParams["equipmentRecord.product.family.productType"] =
      data["equipmentRecord.product.family.productType"];
    queryParams["equipmentRecord.product"] = data["equipmentRecord.product"];
    queryParams.airport = data.airport;
    queryParams.late = data.late;
    queryParams.factoryFlag = data.factoryFlag;
    queryParams.factoryFlagRecentlyClosed = data.factoryFlagRecentlyClosed;
    queryParams.surveyAnswerThisMonth = data.surveyAnswerThisMonth;
    queryParams["equipmentRecord.buyer"] = data["equipmentRecord.buyer"];
    queryParams["airport.country"] = data["airport.country"];
    queryParams["parts.partNumber"] = data["parts.partNumber"];
    queryParams["equipmentRecord.endUser"] = data["equipmentRecord.endUser"];
    queryParams["sparePartsRequests.parts.partNumber"] =
      data["sparePartsRequests.parts.partNumber"];
    queryParams["equipmentRecord.maintainer"] =
      data["equipmentRecord.maintainer"];
    queryParams.confidential = data.confidential;
    queryParams.title = data.title;
    queryParams.technician = data.technician;
    queryParams.errorCodes = data.errorCodes;
    queryParams.actor = data.actor;

    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_calls?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllTechnicalOnCallsResponse>;
  } catch (error) {
    return handleApiError<IGetAllTechnicalOnCallsResponse>(error);
  }
};
