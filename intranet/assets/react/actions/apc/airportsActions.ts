import { APC_FETCH_AIRPORT, APC_FETCH_AIRPORTS } from "../../constants";

export function fetchAirport(iri: any, form: any, type = APC_FETCH_AIRPORT) {
  return {
    type,
    form,
    payload: {
      request: {
        url: iri,
      },
    },
  };
}

export function fetchAirports(search: any) {
  return {
    type: APC_FETCH_AIRPORTS,
    payload: {
      request: {
        url: `/airports?normalization_groups_override[]=airport_list&order[code]=asc&q=${search}`,
      },
    },
  };
}
