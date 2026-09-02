import {
  IPostTechnicianOnCallAndFilesApiPayload,
  postTechnicianOnCallAndFiles,
} from "../api/postTechnicianOnCallAndFiles";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPostTocAndFiles {
  id: string;
  payload: IPostTechnicianOnCallAndFilesApiPayload;
}

export const handlePostTocAndFilesEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedPostTocAndFiles> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_post_toc_and_files
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_post_toc_and_files, data.id);
    return postTechnicianOnCallAndFiles(data.payload);
  });
  const responses = await Promise.all(promises);
  responses.forEach((response) => {
    const tocId = response.data?.id?.toString();
    const csrId = response.data?.["@sub_resources"]?.customerServiceRecord?.id;
    let message = "";
    if (tocId && csrId) {
      message = `TOC #${tocId} & CSR #${csrId} Created Successfully.`;
    } else if (tocId) {
      message = `TOC #${tocId} Created Successfully.`;
    } else {
      message = "An error occurred while creating TOC. Please try again later.";
    }
    self.registration.showNotification(message);
  });
};
