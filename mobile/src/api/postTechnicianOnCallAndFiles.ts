import { StatusCodes } from "http-status-codes";

import { handleApiError } from "../utils/api";
import {
  IPostTechnicianOnCallApiPayload,
  postTechnicianOnCall,
} from "./postTechnicianOnCall";
import { IPostTechnicianOnCallFiles } from "../@type/IPostTechnicianOnCallFiles";
import { postTechnicianOnCallFiles } from "./postTechnicianOnCallFiles";
import { IPostTechnicianOnCallApiResponse } from "../@type/IPostTechnicalOnCallApiResponse";

export interface IPostTechnicianOnCallAndFilesApiPayload
  extends IPostTechnicianOnCallFiles {
  tocData: IPostTechnicianOnCallApiPayload;
}

export const postTechnicianOnCallAndFiles = async (
  data: IPostTechnicianOnCallAndFilesApiPayload
) => {
  try {
    const response = await postTechnicianOnCall(data.tocData);
    if (
      (response.status === StatusCodes.CREATED ||
        response.status === StatusCodes.PARTIAL_CONTENT) &&
      response.data
    ) {
      const tocId = response.data.id?.toString() ?? "";
      await postTechnicianOnCallFiles({
        tocId,
        files: data.files,
        mainFile: data.mainFile,
      });
    }
    return response;
  } catch (error) {
    return handleApiError<IPostTechnicianOnCallApiResponse>(error);
  }
};
