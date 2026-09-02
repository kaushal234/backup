import {
  PRODUCT_MANUFACTURING_CLEAR_DATA,
  PRODUCT_MANUFACTURING_UPDATE_PRODUCT,
} from "../../constants";

export function clearData(product: any, factory: any, index: any, year: any) {
  return {
    type: PRODUCT_MANUFACTURING_CLEAR_DATA,
    product,
    factory,
    index,
    year,
  };
}

export function updateProductManufacturing(product: any, index: any) {
  return {
    type: PRODUCT_MANUFACTURING_UPDATE_PRODUCT,
    payload: {
      url: `sales/products/${product.id}?normalization_groups_override[]=product_manufacturing`,
      body: product,
    },
    index,
  };
}
