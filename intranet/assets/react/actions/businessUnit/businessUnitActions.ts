import { DIRECTORY_FETCH_BUSINESS_UNITS } from "../../constants";

export function fetchBusinessUnits() {
  return {
    type: DIRECTORY_FETCH_BUSINESS_UNITS,
    payload: {
      request: {
        url: "/business_units?order[name]=asc",
      },
    },
  };
}
