import qs from "qs";
import { client } from "../store";
import { IBasicApiResponse } from "../types/IBasicApiResponse";
import { handleApiError } from "../utils/api";
import { ITechnicianOnCall } from "../types/IGetTechnicalOnCallsResponse";

interface IFetchTechnicianOnCallParams {
  tocId: string;
}

const DEFAULT_QUERY_PARAMS = {
  normalizationGroups: ["workflow", "tracking"],
};

export const fetchTechnicianOnCall = async (
  params: IFetchTechnicianOnCallParams
) => {
  try {
    const queryParams = DEFAULT_QUERY_PARAMS;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(
      `/service/technician_on_calls/${params.tocId}?${queryString}`
    );
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<ITechnicianOnCall>;
  } catch (error) {
    return handleApiError<ITechnicianOnCall>(error);
  }
};
