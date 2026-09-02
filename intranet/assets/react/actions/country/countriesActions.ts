import { API_FETCH_COUNTRY, API_FETCH_COUNTRIES } from "../../constants";

export function fetchCountry(iri: any) {
  return {
    type: API_FETCH_COUNTRY,
    payload: {
      request: {
        url: iri,
      },
    },
  };
}

export function fetchCountries(search: any) {
  return {
    type: API_FETCH_COUNTRIES,
    payload: {
      request: {
        url: `/countries?normalization_groups_override[]=country_list&order[name]=asc&q=${search}`,
      },
    },
  };
}
