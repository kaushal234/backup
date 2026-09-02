import {
  IPostCsrHourMeterApiPayload,
  postCsrHourMeter,
} from "../api/postCsrHourMeter";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPostCsrHourMeter {
  id: string;
  payload: IPostCsrHourMeterApiPayload;
}

export const handlePostCsrHourMeterEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedPostCsrHourMeter> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_post_csr_hour_meter
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_post_csr_hour_meter, data.id);
    return postCsrHourMeter(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some((response) => !response.data);
  self.registration.showNotification(
    hasError
      ? "An error occurred while updating Hour Meter. Please try again later."
      : "Hour Meter Updated Successfully."
  );
};
