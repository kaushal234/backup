import { MIS_FETCH_MODULE, MIS_FETCH_MODULES } from "../../constants";

export function fetchModulesForTroubleTickets(application: any) {
  return {
    type: MIS_FETCH_MODULES,
    payload: {
      request: {
        url: `/modules?application=${application}&order[name]=ASC&status=ACTIVE&disabledForTroubleTicket=false`,
      },
    },
  };
}

export function fetchModules() {
  return {
    type: MIS_FETCH_MODULES,
    payload: {
      request: {
        url: `/modules`,
      },
    },
  };
}

export function fetchModule(
  iri: any,
  form: any = undefined,
  formType: any = "add"
) {
  return {
    type: MIS_FETCH_MODULE,
    payload: {
      form,
      formType,
      request: {
        url: iri,
      },
    },
  };
}
