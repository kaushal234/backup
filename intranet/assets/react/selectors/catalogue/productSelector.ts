import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getProducts = (state: RootState) => {
  return state.product.products;
};

export const getProductsMapping = createSelector([getProducts], (products) => {
  if (!products) {
    return [];
  }
  return Object.values(products).map((product: any, index) => {
    const financeFamily = !product.financeFamily
      ? null
      : {
          value: product.financeFamily["@id"],
          label: product.financeFamily.name,
        };
    product = {
      ...product,
      value: _.get(product, "@id"),
      label: _.get(product, "name"),
    };
    return {
      ...product,
      index,
      financeFamily,
    };
  });
});

export const getProductsManufacturingMapping = createSelector(
  [getProducts],
  (products) => {
    if (!products) {
      return [];
    }
    return Object.values(products).map((product: any) => {
      Object.keys(product.productManufacturings).map((key) => {
        if (product.productManufacturings[key].length === 0) {
          product.productManufacturings[key] = {};
        }
        return product.productManufacturings;
      });
      return {
        ...product,
        value: _.get(product, "@id"),
        label: _.get(product, "name"),
      };
    });
  }
);
