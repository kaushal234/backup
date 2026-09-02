import { StatusCodes } from "http-status-codes";
import {
  deleteTechnicianOnCall,
  IDeleteTechnicianOnCallApiPayload,
} from "../api/deleteTechnicianOnCall";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedDeleteToc {
  id: string;
  payload: IDeleteTechnicianOnCallApiPayload;
}

export const handleDeleteTocEvent = async (self: ServiceWorkerGlobalScope) => {
  const dataArray: Array<ISavedDeleteToc> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_delete_toc
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_delete_toc, data.id);
    return deleteTechnicianOnCall(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some(
    (response) => !(response.status === StatusCodes.NO_CONTENT)
  );
  self.registration.showNotification(
    hasError
      ? "An error occurred while deleting TOC. Please try again later."
      : "TOC Deleted Successfully."
  );
};
