import { NON_CONFORMITY_FETCH_NON_CONFORMITIES } from "../../constants";

export function fetchNonConformitiesList(search: any) {
  return {
    type: NON_CONFORMITY_FETCH_NON_CONFORMITIES,
    payload: {
      request: {
        url: `/quality/non_conformities?id=${search}`,
      },
    },
  };
}
