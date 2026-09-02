import { MIS_FETCH_TYPES, MIS_FETCH_TYPE } from "../../constants";

export function fetchTypes(type: any) {
  return {
    type: MIS_FETCH_TYPES,
    payload: {
      request: {
        url: `/mis/types?type=${type}&order[displayedOrder]=ASC`,
      },
    },
  };
}

export function fetchType(id: any) {
  return {
    type: MIS_FETCH_TYPE,
    payload: {
      form: "trouble_ticket_form",
      request: {
        url: `/mis/types/${id}`,
      },
    },
  };
}
