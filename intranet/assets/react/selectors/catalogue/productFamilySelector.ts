import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getProductFamilies = (state: RootState) => {
  return state.productFamily.productFamilies;
};

const buildNameOption = (productFamily: any) => ({
  value: _.get(productFamily, "@id"),
  label: productFamily.name,
});

export const getProductFamiliesMapping = createSelector(
  [getProductFamilies],
  (productFamilies) => {
    if (!productFamilies) {
      return [];
    }
    return Object.values(productFamilies).map((productFamily) =>
      buildNameOption(productFamily)
    );
  }
);
