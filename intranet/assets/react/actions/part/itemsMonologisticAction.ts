import {
  PART_FETCH_ITEM_MONOLOGISTIC,
  PART_FETCH_ITEMS_MONOLOGISTIC,
} from "../../constants";

export function fetchItemMonologistic(
  index: any,
  partNumber: any,
  form: any,
  type = PART_FETCH_ITEM_MONOLOGISTIC
) {
  partNumber = encodeURIComponent(encodeURIComponent(partNumber));
  return {
    type,
    form,
    payload: {
      index,
      request: {
        url: `/ion/item_monologistics/${partNumber}?selection[]=description&selection[]=itemCode&selection[]=baseUOM`,
      },
    },
  };
}

export function fetchItemsMonologistic(
  partNumber: any,
  type = PART_FETCH_ITEMS_MONOLOGISTIC
) {
  partNumber = encodeURIComponent(encodeURIComponent(partNumber));
  return {
    type,
    payload: {
      request: {
        url: `/ion/item_monologistics?selection[]=description&selection[]=itemCode&itemCode[like]=%25${partNumber}%25`,
      },
    },
  };
}
