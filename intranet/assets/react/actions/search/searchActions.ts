import { SEARCH_SET_FILTER_TEXT } from "../../constants";

export function setSearchText(text: any) {
  return {
    type: SEARCH_SET_FILTER_TEXT,
    payload: {
      filterText: text,
    },
  };
}
