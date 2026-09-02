import { CURRENCY_FETCH_CURRENCIES } from "../../constants";

export function fetchCurrencies() {
  return {
    type: CURRENCY_FETCH_CURRENCIES,
    payload: {
      request: {
        url: "/finance/currencies",
      },
    },
  };
}
