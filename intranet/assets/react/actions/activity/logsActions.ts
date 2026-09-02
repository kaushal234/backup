import { ACTIVITY_FETCH_LOGS } from "../../constants";

export function getLogs(iri: any) {
  return {
    type: ACTIVITY_FETCH_LOGS,
    payload: {
      url: `/logs?resource=${iri}&pagination=false`,
      iri,
    },
  };
}
