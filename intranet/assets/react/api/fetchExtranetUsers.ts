import qs from "qs";
import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IFetchExtranetUsersResponse } from "../types/IFetchExtranetUsersResponse";
import { IFetchExtranetUsersPayload } from "../types/IFetchExtranetUsersPayload";

export async function fetchExtranetUsers({
  page,
  pageSize,
  sortModel,
  searchQuery,
  customers,
  locations,
}: IFetchExtranetUsersPayload): Promise<
  IApiResponse<IFetchExtranetUsersResponse>
> {
  try {
    const queryParams: Record<string, string | number | Array<string>> = {
      page,
      itemsPerPage: pageSize,
    };

    if (searchQuery) {
      queryParams.q = searchQuery;
    }

    if (sortModel) {
      queryParams[`order[${sortModel.field ?? "id"}]`] =
        sortModel.sort ?? "asc";
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
      data: {
        items: response.data["hydra:member"],
        total: response.data["hydra:totalItems"] || 0,
      },
    };
  } catch (error) {
    return handleError(error);
  }
}
