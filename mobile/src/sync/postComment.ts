import {
  IPostCommentByTocIdApiPayload,
  postCommentById,
} from "../api/postCommentById";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPostComment {
  id: string;
  payload: IPostCommentByTocIdApiPayload;
}

export const handlePostCommentSyncEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedPostComment> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_post_comment
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_post_comment, data.id);
    return postCommentById(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some((response) => !response.data);
  self.registration.showNotification(
    hasError
      ? "An error occurred while uploading your logs. Please try again later."
      : "Logs Uploaded Successfully."
  );
};
