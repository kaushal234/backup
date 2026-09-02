import {
  IPutTechnicianOnCallApiPayload,
  putTechnicianOnCall,
} from "../api/putTechnicianOnCall";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPutToc {
  id: string;
  payload: IPutTechnicianOnCallApiPayload;
}

export const handlePutTocEvent = async (self: ServiceWorkerGlobalScope) => {
  const dataArray: Array<ISavedPutToc> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_put_toc
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_put_toc, data.id);
    return putTechnicianOnCall(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some((response) => !response.data);
  self.registration.showNotification(
    hasError
      ? "An error occurred while updating TOC. Please try again later."
      : "TOC Updated Successfully."
  );
};
