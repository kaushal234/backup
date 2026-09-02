import {
  IPostTechnicianOnCallPartsApiPayload,
  postTechnicianOnCallParts,
} from "../api/postTechnicianOnCallParts";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPostTocParts {
  id: string;
  payload: IPostTechnicianOnCallPartsApiPayload;
}

export const handlePostTocPartsEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedPostTocParts> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_post_toc_parts
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_post_toc_parts, data.id);
    return postTechnicianOnCallParts(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some((response) => !response.data);
  self.registration.showNotification(
    hasError
      ? "An error occurred while creating TOC Parts. Please try again later."
      : "TOC Parts Created Successfully."
  );
};
