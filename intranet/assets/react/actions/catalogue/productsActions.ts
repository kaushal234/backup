import {
  CATALOGUE_FETCH_PRODUCT,
  CATALOGUE_FETCH_PRODUCTS,
  CATALOGUE_UPDATE_PRODUCT,
} from "../../constants";

export function updateProduct(product: any) {
  return {
    type: CATALOGUE_UPDATE_PRODUCT,
    payload: {
      url: `/sales/products/${product.id}`,
      body: product,
    },
  };
}

export function fetchProduct(iri: any) {
  return {
    type: CATALOGUE_FETCH_PRODUCT,
    payload: {
      request: {
        url: iri,
      },
    },
  };
}

export function fetchProducts(search: any) {
  return {
    type: CATALOGUE_FETCH_PRODUCTS,
    payload: {
      request: {
        url: `/sales/products?normalization_groups_override[]=product_list_pricing&order[name]=asc&hidden=0&q=${search}`,
      },
    },
  };
}
