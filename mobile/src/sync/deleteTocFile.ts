import { StatusCodes } from "http-status-codes";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";
import {
  deleteTechnicianOnCallFile,
  IDeleteTechnicianOnCallFileApiPayload,
} from "../api/deleteTechnicianOnCallFile";

interface ISavedDeleteTocFile {
  id: string;
  payload: IDeleteTechnicianOnCallFileApiPayload;
}

export const handleDeleteTocFileEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedDeleteTocFile> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_delete_toc_file
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_delete_toc_file, data.id);
    return deleteTechnicianOnCallFile(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some(
    (response) => !(response.status === StatusCodes.NO_CONTENT)
  );
  self.registration.showNotification(
    hasError
      ? "An error occurred while deleting TOC File. Please try again later."
      : "TOC File Deleted Successfully."
  );
};
