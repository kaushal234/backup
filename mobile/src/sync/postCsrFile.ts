import {
  IPostCsrFileByCsrIdApiPayload,
  postCsrFileByCsrId,
} from "../api/postCsrFileByCsrId";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPostCsrFile {
  id: string;
  payload: IPostCsrFileByCsrIdApiPayload;
}

export const handlePostCsrFileSyncEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedPostCsrFile> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_post_csr_file
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_post_csr_file, data.id);
    return postCsrFileByCsrId(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some((response) => !response.data);
  self.registration.showNotification(
    hasError
      ? "An error occurred while uploading CSR Files. Please try again later."
      : "CSR Files Uploaded Successfully."
  );
};
