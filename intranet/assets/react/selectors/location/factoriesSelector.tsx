import { createSelector } from "reselect";
import { RootState } from "../../store";
import { ILocationState } from "../../types/ILocationState";

const getFactories = (
  state: RootState,
  factories: keyof ILocationState = "factories"
) => {
  return state.location[factories];
};

export const getFactoriesMapping = createSelector(
  [getFactories],
  (factories) => {
    if (!factories) {
      return [];
    }
    return Object.values(factories).map((factory: any) => {
      return {
        erp: factory.erp,
        value: factory["@id"],
        label: `${factory.name} - ${
          factory.erp !== null ? factory.erp : "N/A"
        }`,
      };
    });
  }
);
