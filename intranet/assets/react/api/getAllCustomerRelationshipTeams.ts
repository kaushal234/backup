import qs from "qs";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllCustomerRelationshipTeamsResponse } from "../types/IGetAllCustomerRelationshipTeamsResponse";
import { handleError } from "../utils/utils";

interface IGetAllCustomerRelationshipTeamsApiPayload {
  customerIri?: string;
}

interface IGetAllCustomerRelationshipTeamsApiParams {
  customer?: string;
}

export const getAllCustomerRelationshipTeams = async (
  data: IGetAllCustomerRelationshipTeamsApiPayload
): Promise<IApiResponse<IGetAllCustomerRelationshipTeamsResponse>> => {
  try {
    const queryParams: IGetAllCustomerRelationshipTeamsApiParams = {};
    // filter
    queryParams.customer = data.customerIri;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(
      `/sales/customer_relationship_teams?${queryString}`
    );
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
