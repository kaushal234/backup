import {
  IPostTocSparePartsRequestApiPayload,
  postTocSparePartsRequest,
} from "../api/postTocSparePartsRequest";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPostTocSpr {
  id: string;
  payload: IPostTocSparePartsRequestApiPayload;
}

export const handlePostTocSprEvent = async (self: ServiceWorkerGlobalScope) => {
  const dataArray: Array<ISavedPostTocSpr> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_post_toc_spr
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_post_toc_spr, data.id);
    return postTocSparePartsRequest(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some((response) => !response.data);
  self.registration.showNotification(
    hasError
      ? "An error occurred while creating TOC SPR. Please try again later."
      : "TOC SPR Created Successfully."
  );
};
