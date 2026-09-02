import { StatusCodes } from "http-status-codes";
import {
  deleteTechnicianOnCallParts,
  IDeleteTechnicianOnCallPartsApiPayload,
} from "../api/deleteTechnicianOnCallParts";
import { IDB_DATABASE } from "../constants/constants";
import { idbDeleteItem, idbGetAllItems } from "../idb";

interface ISavedDeleteTocParts {
  id: string;
  payload: IDeleteTechnicianOnCallPartsApiPayload;
}

export const handleDeleteTocPartsEvent = async (
  self: ServiceWorkerGlobalScope
) => {
  const dataArray: Array<ISavedDeleteTocParts> = await idbGetAllItems(
    IDB_DATABASE.stores.sync_delete_toc_parts
  );
  const promises = dataArray.map((data) => {
    idbDeleteItem(IDB_DATABASE.stores.sync_delete_toc_parts, data.id);
    return deleteTechnicianOnCallParts(data.payload);
  });
  const responses = await Promise.all(promises);
  const hasError = responses.some(
    (response) => !(response.status === StatusCodes.NO_CONTENT)
  );
  self.registration.showNotification(
    hasError
      ? "An error occurred while deleting TOC Parts. Please try again later."
      : "TOC Parts Deleted Successfully."
  );
};
