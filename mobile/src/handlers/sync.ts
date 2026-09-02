import { IDB_DATABASE } from "../constants/constants";
import { handleDeleteTocEvent } from "../sync/deleteToc";
import { handleDeleteTocFileEvent } from "../sync/deleteTocFile";
import { handleDeleteTocPartsEvent } from "../sync/deleteTocParts";
import { handlePostCommentSyncEvent } from "../sync/postComment";
import { handlePostExtranetUserEvent } from "../sync/postExtranetUser";
import { handlePostCsrFileSyncEvent } from "../sync/postCsrFile";
import { handlePostCsrHourMeterEvent } from "../sync/postCsrHourMeter";
import { handlePostTocAndFilesEvent } from "../sync/postTocAndFiles";
import { handlePostTocFileSyncEvent } from "../sync/postTocFile";
import { handlePostTocPartsEvent } from "../sync/postTocParts";
import { handlePostTocSprEvent } from "../sync/postTocSpr";
import { handlePutCsrEvent } from "../sync/putCsr";
import { handlePutInterventionEvent } from "../sync/putIntervention";
import { handlePutRequestTechnicianEvent } from "../sync/putRequestTechnician";
import { handlePutTocEvent } from "../sync/putToc";
import { handlePutTocPartsEvent } from "../sync/putTocParts";
import { handlePutTocStatusEvent } from "../sync/putTocStatus";

export const handleSync = async (
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  event: any,
  self: ServiceWorkerGlobalScope
) => {
  switch (event.tag) {
    case IDB_DATABASE.stores.sync_post_comment: {
      await handlePostCommentSyncEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_post_toc_files: {
      await handlePostTocFileSyncEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_post_csr_file: {
      await handlePostCsrFileSyncEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_put_toc: {
      await handlePutTocEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_put_toc_status: {
      await handlePutTocStatusEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_put_request_technician: {
      await handlePutRequestTechnicianEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_post_toc_and_files: {
      await handlePostTocAndFilesEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_post_toc_parts: {
      await handlePostTocPartsEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_put_toc_parts: {
      await handlePutTocPartsEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_delete_toc_parts: {
      await handleDeleteTocPartsEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_put_csr: {
      await handlePutCsrEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_put_intervention: {
      await handlePutInterventionEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_post_csr_hour_meter: {
      await handlePostCsrHourMeterEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_delete_toc: {
      await handleDeleteTocEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_post_toc_spr: {
      await handlePostTocSprEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_delete_toc_file: {
      await handleDeleteTocFileEvent(self);
      break;
    }
    case IDB_DATABASE.stores.sync_post_extranet_user: {
      await handlePostExtranetUserEvent(self);
      break;
    }
    default: {
      // empty on purpose
    }
  }
};
