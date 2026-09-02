import { handleApiError } from "../utils/api";
import { postTocFileByTocId } from "./postTocFileByTocId";
import { postTocMainFileByTocId } from "./postTocMainFileByTocId";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostTechnicianOnCallFiles } from "../@type/IPostTechnicianOnCallFiles";

export interface IPostTechnicianOnCallFilesApiPayload
  extends IPostTechnicianOnCallFiles {
  tocId: string;
}

export const postTechnicianOnCallFiles = async (
  data: IPostTechnicianOnCallFilesApiPayload
) => {
  try {
    const promises = [];
    if (data.mainFile) {
      const mainFilePromise = postTocMainFileByTocId({
        ...data.mainFile,
        tocId: data.tocId,
      });
      promises.push(mainFilePromise);
    }
    if (data.files?.length) {
      data.files.forEach((file) => {
        const filePromise = postTocFileByTocId({ ...file, tocId: data.tocId });
        promises.push(filePromise);
      });
    }
    const responses = await Promise.all(promises);
    return {
      status: responses?.[0]?.status ?? 200,
      data: null,
    } as IBasicApiResponse<null>;
  } catch (error) {
    return handleApiError<null>(error);
  }
};
