import { createSelector } from "reselect";
import { RootState } from "../../store";
import { IFinanceFamilyState } from "../../reducers/finance/financeFamilyReducer";

const getFinanceFamilies = (
  state: RootState,
  financeFamilies: keyof IFinanceFamilyState = "financeFamilies"
) => {
  return state.finance[financeFamilies];
};

export const getFinanceFamiliesMapping = createSelector(
  [getFinanceFamilies],
  (financeFamilies) => {
    if (!financeFamilies) {
      return [];
    }
    return financeFamilies.map((financeFamily: any) => {
      if (financeFamily.pricings && financeFamily.pricings.length === 0) {
        financeFamily.pricings = {};
      }
      return {
        ...financeFamily,
        value: financeFamily["@id"],
        label: financeFamily.name,
      };
    });
  }
);

const getFinanceFamily = (
  state: RootState,
  index: any,
  financeFamilies: keyof IFinanceFamilyState = "financeFamilies"
) => {
  return state.finance[financeFamilies][index];
};

export const getFinanceFamilyMapping = createSelector(
  [getFinanceFamily],
  (financeFamily) => financeFamily
);
