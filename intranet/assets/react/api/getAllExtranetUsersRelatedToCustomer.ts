import qs from "qs";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllExtranetUsersResponse } from "../types/IGetAllExtranetUsersResponse";
import { IPagination } from "../types/IPagination";
import { handleError } from "../utils/utils";

interface IGetAllExtranetUsersRelatedToCustomerApiPayload {
  customerIri: string;
  searchText?: string;
}
interface IGetAllExtranetUsersRelatedToCustomerApiParams extends IPagination {
  relatedToCustomer?: string;
  q?: string;
}

const DEFAULT_QUERY_PARAMS: IGetAllExtranetUsersRelatedToCustomerApiParams = {
  itemsPerPage: 50,
  page: 1,
};

export const getAllExtranetUsersRelatedToCustomer = async (
  data: IGetAllExtranetUsersRelatedToCustomerApiPayload
): Promise<IApiResponse<IGetAllExtranetUsersResponse>> => {
  try {
    const queryParams: IGetAllExtranetUsersRelatedToCustomerApiParams = {
      ...DEFAULT_QUERY_PARAMS,
    };
    // filter
    queryParams.relatedToCustomer = data.customerIri;
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/sales/extranet_users?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
