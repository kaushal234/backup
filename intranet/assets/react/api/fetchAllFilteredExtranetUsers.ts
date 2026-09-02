import qs from "qs";
import { IExtranetUser } from "../types/IExtranetUser";
import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";

interface IFetchAllFilteredExtranetUsersPayload {
  pagination: boolean;
  searchQuery?: string;
  customers?: Array<string>;
  locations?: Array<string>;
}

export async function fetchAllFilteredExtranetUsers({
  pagination,
  searchQuery,
  customers,
  locations,
}: IFetchAllFilteredExtranetUsersPayload): Promise<
  IApiResponse<Array<IExtranetUser>>
> {
  try {
    const queryParams: Record<string, boolean | string | Array<string>> = {
      pagination,
    };

    if (searchQuery) {
      queryParams.q = searchQuery;
    }
    if (customers) {
      queryParams["extranetUserProfile.customer.name"] = customers;
    }

    if (locations) {
      queryParams["extranetUserProfile.erpLocation.name"] = locations;
    }
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(
      `/sales/extranet_users?hidden=false&extranetUserProfile.archived=false&${queryString}`
    );

    return {
      status: 200,
      data: response.data["hydra:member"],
    };
  } catch (error) {
    return handleError(error);
  }
}
