import {
  IPutRequestTechnicianApiPayload,
  putRequestTechnician,
} from "../api/putRequestTechnician";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPutRequestTechnician {
  id: string;
  payload: IPutRequestTechnicianApiPayload;
}

export const handlePutRequestTechnicianEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedPutRequestTechnician> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_put_request_technician
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_put_request_technician, data.id);
    return putRequestTechnician(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some((response) => !response.data);
  self.registration.showNotification(
    hasError
      ? "An error occurred while updating TOC. Please try again later."
      : "TOC Updated Successfully."
  );
};
