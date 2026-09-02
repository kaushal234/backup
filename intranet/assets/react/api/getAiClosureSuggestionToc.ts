import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAiClosureSuggestionTocApiResponse } from "../types/IGetAiClosureSuggestionTocApiResponse";
import { handleError } from "../utils/utils";

interface IGetAiClosureSuggestionTocApiParams {
  id?: string;
}

export const getAiClosureSuggestionToc = async ({
  id,
}: IGetAiClosureSuggestionTocApiParams): Promise<
  IApiResponse<IGetAiClosureSuggestionTocApiResponse>
> => {
  try {
    const response = await client.get(
      `service/ai/suggest-closure/technician_on_calls/${id}`
    );
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
