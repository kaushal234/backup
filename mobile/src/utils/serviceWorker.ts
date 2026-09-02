import { IPostCommentByTocIdApiPayload } from "../api/postCommentById";
import { IPostCsrFileByCsrIdApiPayload } from "../api/postCsrFileByCsrId";
import { IPutTechnicianOnCallApiPayload } from "../api/putTechnicianOnCall";
import { idbCreateItem } from "../idb";
import { IDB_DATABASE } from "../constants/constants";
import { generateRandomString } from "./utils";
import { IPostTechnicianOnCallPartsApiPayload } from "../api/postTechnicianOnCallParts";
import { IPutTechnicianOnCallPartsApiPayload } from "../api/putTechnicianOnCallParts";
import { IDeleteTechnicianOnCallPartsApiPayload } from "../api/deleteTechnicianOnCallParts";
import { IPutCustomerServiceRecordApiPayload } from "../api/putCustomerServiceRecord";
import { IPutInterventionApiPayload } from "../api/putIntervention";
import { IPostCsrHourMeterApiPayload } from "../api/postCsrHourMeter";
import { IPutTechnicianOnCallStatusApiPayload } from "../api/putTechnicianOnCallStatus";
import { IPutRequestTechnicianApiPayload } from "../api/putRequestTechnician";
import { IPostTechnicianOnCallFilesApiPayload } from "../api/postTechnicianOnCallFiles";
import { IPostTechnicianOnCallAndFilesApiPayload } from "../api/postTechnicianOnCallAndFiles";
import { IDeleteTechnicianOnCallApiPayload } from "../api/deleteTechnicianOnCall";
import { IPostTocSparePartsRequestApiPayload } from "../api/postTocSparePartsRequest";
import { IDeleteTechnicianOnCallFileApiPayload } from "../api/deleteTechnicianOnCallFile";
import { IPostExtranetUserApiPayload } from "../api/postExtranetUser";

type IRegisterSyncEventWithApiPayloadParams =
  | {
      eventName: typeof IDB_DATABASE.stores.sync_post_comment;
      payload: IPostCommentByTocIdApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_post_toc_files;
      payload: IPostTechnicianOnCallFilesApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_post_toc_and_files;
      payload: IPostTechnicianOnCallAndFilesApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_post_csr_file;
      payload: IPostCsrFileByCsrIdApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_put_toc;
      payload: IPutTechnicianOnCallApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_delete_toc;
      payload: IDeleteTechnicianOnCallApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_put_toc_status;
      payload: IPutTechnicianOnCallStatusApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_put_request_technician;
      payload: IPutRequestTechnicianApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_post_toc_parts;
      payload: IPostTechnicianOnCallPartsApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_put_toc_parts;
      payload: IPutTechnicianOnCallPartsApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_delete_toc_parts;
      payload: IDeleteTechnicianOnCallPartsApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_put_csr;
      payload: IPutCustomerServiceRecordApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_put_intervention;
      payload: IPutInterventionApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_post_csr_hour_meter;
      payload: IPostCsrHourMeterApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_post_toc_spr;
      payload: IPostTocSparePartsRequestApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_delete_toc_file;
      payload: IDeleteTechnicianOnCallFileApiPayload;
    }
  | {
      eventName: typeof IDB_DATABASE.stores.sync_post_extranet_user;
      payload: IPostExtranetUserApiPayload;
    };

export const registerSyncEvent = (eventName: string) => {
  navigator.serviceWorker.ready.then((sw) => sw.sync?.register?.(eventName));
};

export const registerSyncEventWithApiPayload = async (
  params: IRegisterSyncEventWithApiPayloadParams
) => {
  const payloadId = generateRandomString();
  await idbCreateItem(params.eventName, {
    id: payloadId,
    payload: params.payload,
  });
  registerSyncEvent(params.eventName);
};
