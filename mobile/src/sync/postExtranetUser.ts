import {
  IPostExtranetUserApiPayload,
  postExtranetUser,
} from "../api/postExtranetUser";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedPostExtranetUser {
  id: string;
  payload: IPostExtranetUserApiPayload;
}

export const handlePostExtranetUserEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedPostExtranetUser> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_post_extranet_user
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_post_extranet_user, data.id);
    return postExtranetUser(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some((response) => !response.data);
  self.registration.showNotification(
    hasError
      ? "An error occurred while creating user. Please try again later."
      : "User Created Successfully."
  );
};
