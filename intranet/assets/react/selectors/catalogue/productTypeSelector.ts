import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getProducTypes = (state: RootState) => {
  return state.productType.productTypes;
};

export const buildNameOption = (productType: any) => ({
  value: _.get(productType, "@id"),
  label: _.get(productType, "englishName"),
});

export const getProductTypesMapping = createSelector(
  [getProducTypes],
  (productTypes) => {
    if (!productTypes) {
      return [];
    }
    return Object.values(productTypes).map((productType) =>
      buildNameOption(productType)
    );
  }
);
