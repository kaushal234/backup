import { LOCATION_FETCH_FACTORIES } from "../../constants";

export function fetchFactories() {
  return {
    type: LOCATION_FETCH_FACTORIES,
    url: "/locations?capability.factory=true&state.hidden=false&order[name]=ASC",
  };
}
