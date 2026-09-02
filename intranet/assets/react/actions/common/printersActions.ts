import { COMMON_FETCH_PRINTERS, RESET } from "../../constants";

export function fetchPrinters(type: any = null, erp: any = null) {
  let url = `/printers?type=${type}`;

  if (erp !== null) {
    url += `&erp=${erp}`;
  }

  return {
    type: COMMON_FETCH_PRINTERS,
    payload: {
      request: {
        url,
      },
    },
  };
}

export function resetPrinters() {
  return { type: COMMON_FETCH_PRINTERS + RESET };
}
