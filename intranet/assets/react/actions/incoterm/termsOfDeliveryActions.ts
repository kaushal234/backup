import { INCOTERM_FETCH_TERMS_OF_DELIVERIES } from "../../constants";

export function fetchTermsOfDeliveries() {
  return {
    type: INCOTERM_FETCH_TERMS_OF_DELIVERIES,
    payload: {
      request: {
        url: "/sales/incoterms",
      },
    },
  };
}
