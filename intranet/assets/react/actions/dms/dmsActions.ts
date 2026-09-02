import { DMS_FETCH_DMS } from "../../constants";

export function fetchDMS(search: any) {
  return {
    type: DMS_FETCH_DMS,
    payload: {
      request: {
        url: `/dms?normalization_groups_override[]=document_list&normalization_groups_override[]=expose_legacy&q=${search}`,
      },
    },
  };
}
