import { createSelector } from "reselect";

const getCurrencies = (state: any) => {
  return state.currency.currencies;
};

export const getCurrenciesMapping = createSelector(
  [getCurrencies],
  (currencies) => {
    if (!currencies) {
      return [];
    }
    return currencies.map((currency: any) => ({
      value: currency["@id"],
      label: `${currency.name}`,
    }));
  }
);
