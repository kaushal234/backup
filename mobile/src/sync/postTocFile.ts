import { StatusCodes } from "http-status-codes";
import {
  IPostTechnicianOnCallFilesApiPayload,
  postTechnicianOnCallFiles,
} from "../api/postTechnicianOnCallFiles";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPostTechnicianOnCallFiles {
  id: string;
  payload: IPostTechnicianOnCallFilesApiPayload;
}

export const handlePostTocFileSyncEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedPostTechnicianOnCallFiles> =
    await idbGetAllItems(IDB_DATABASE.stores.sync_post_toc_files);
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_post_toc_files, data.id);
    return postTechnicianOnCallFiles(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some(
    (response) => response.status !== StatusCodes.CREATED
  );
  self.registration.showNotification(
    hasError
      ? "An error occurred while uploading TOC Files. Please try again later."
      : "TOC Files Uploaded Successfully."
  );
};
