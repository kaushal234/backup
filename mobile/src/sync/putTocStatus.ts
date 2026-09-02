import {
  IPutTechnicianOnCallStatusApiPayload,
  putTechnicianOnCallStatus,
} from "../api/putTechnicianOnCallStatus";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPutTocStatus {
  id: string;
  payload: IPutTechnicianOnCallStatusApiPayload;
}

export const handlePutTocStatusEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedPutTocStatus> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_put_toc_status
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_put_toc_status, data.id);
    return putTechnicianOnCallStatus(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some((response) => !response.data);
  self.registration.showNotification(
    hasError
      ? "An error occurred while updating TOC. Please try again later."
      : "TOC Updated Successfully."
  );
};
