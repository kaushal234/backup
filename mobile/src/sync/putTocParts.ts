import {
  IPutTechnicianOnCallPartsApiPayload,
  putTechnicianOnCallParts,
} from "../api/putTechnicianOnCallParts";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPutTocParts {
  id: string;
  payload: IPutTechnicianOnCallPartsApiPayload;
}

export const handlePutTocPartsEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedPutTocParts> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_put_toc_parts
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_put_toc_parts, data.id);
    return putTechnicianOnCallParts(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some((response) => !response.data);
  self.registration.showNotification(
    hasError
      ? "An error occurred while updating TOC Parts. Please try again later."
      : "TOC Parts Updated Successfully."
  );
};
