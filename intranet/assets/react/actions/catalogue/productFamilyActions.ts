import { CATALOGUE_FETCH_PRODUCT_FAMILIES } from "../../constants";

export function fetchProductFamilies(search: any) {
  return {
    type: CATALOGUE_FETCH_PRODUCT_FAMILIES,
    payload: {
      request: {
        url: `/sales/product_families?normalization_groups_override[]=catalogue_family_list&order[name]=asc&q=${search}`,
      },
    },
  };
}
