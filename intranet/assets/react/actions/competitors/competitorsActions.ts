import {
  COMPETITORS_FETCH_COMPETITOR,
  COMPETITORS_FETCH_COMPETITORS,
} from "../../constants";

export function fetchCompetitor(iri: any) {
  return {
    type: COMPETITORS_FETCH_COMPETITOR,
    payload: {
      request: {
        url: iri,
      },
    },
  };
}

export function fetchCompetitors(search: any) {
  return {
    type: COMPETITORS_FETCH_COMPETITORS,
    payload: {
      request: {
        url: `/sales/competitors?normalizationGroupsOverride[]=competitor_list&order[name]=asc&q=${search}`,
      },
    },
  };
}
