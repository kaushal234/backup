import {
  IPutInterventionApiPayload,
  putIntervention,
} from "../api/putIntervention";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPutIntervention {
  id: string;
  payload: IPutInterventionApiPayload;
}

export const handlePutInterventionEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedPutIntervention> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_put_intervention
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_put_intervention, data.id);
    return putIntervention(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some((response) => !response.data);
  self.registration.showNotification(
    hasError
      ? "An error occurred while updating Intervention. Please try again later."
      : "Intervention Updated Successfully."
  );
};
