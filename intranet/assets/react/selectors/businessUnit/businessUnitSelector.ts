import { createSelector } from "reselect";
import { RootState } from "../../store";

const getBusinessUnits = (state: RootState) => {
  return state.businessUnit.businessUnits;
};

export const getBusinessUnitsMapping = createSelector(
  [getBusinessUnits],
  (businessUnits) => {
    if (!businessUnits) {
      return [];
    }

    return Object.values(businessUnits).map((businessUnit: any) => ({
      value: businessUnit["@id"],
      label: businessUnit.name,
    }));
  }
);
