import { ERP_FETCH_ITEM, ERP_FETCH_ITEMS } from "../../constants";

export function fetchItem(
  index: any,
  partNumber: any,
  erp: any,
  form: any,
  type = ERP_FETCH_ITEM
) {
  partNumber = encodeURIComponent(encodeURIComponent(partNumber));
  return {
    type,
    form,
    payload: {
      index,
      request: {
        url: `/ion/items/item=${partNumber};site=${erp}`,
      },
    },
  };
}

export function fetchItems(partNumber: any, erp: any, type = ERP_FETCH_ITEMS) {
  partNumber = encodeURIComponent(encodeURIComponent(partNumber));
  return {
    type,
    payload: {
      request: {
        url: `/ion/items?itemFilter=${partNumber}&site=${erp}`,
      },
    },
  };
}

export function fetchSageItems(partNumber: any, type = ERP_FETCH_ITEMS) {
  partNumber = encodeURIComponent(encodeURIComponent(partNumber));
  return {
    type,
    payload: {
      request: {
        url: `/sageparts/parts?contains[item]=${partNumber}`,
      },
    },
  };
}
