import { createSelector } from "reselect";

const getTermsOfDelivery = (state: any) => {
  return state.incoterms.termsOfDelivery;
};

export const getTermsOfDeliveryMapping = createSelector(
  [getTermsOfDelivery],
  (termsOfDelivery) => {
    if (!termsOfDelivery) {
      return [];
    }
    return termsOfDelivery.map((term: any) => ({
      value: term["@id"],
      label: `${term.description}`,
    }));
  }
);
