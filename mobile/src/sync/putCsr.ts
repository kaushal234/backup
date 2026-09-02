import {
  IPutCustomerServiceRecordApiPayload,
  putCustomerServiceRecord,
} from "../api/putCustomerServiceRecord";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPutCsr {
  id: string;
  payload: IPutCustomerServiceRecordApiPayload;
}

export const handlePutCsrEvent = async (self: ServiceWorkerGlobalScope) => {
  const dataArray: Array<ISavedPutCsr> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_put_csr
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_put_csr, data.id);
    return putCustomerServiceRecord(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some((response) => !response.data);
  self.registration.showNotification(
    hasError
      ? "An error occurred while updating CSR. Please try again later."
      : "CSR Updated Successfully."
  );
};
