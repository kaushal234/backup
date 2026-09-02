import {
  SPR_CREATE_SB_SPARE_PARTS_REQUEST,
  SPR_CREATE_TOC_SPARE_PARTS_REQUEST,
  SPR_EDIT_SPARE_PARTS_REQUEST_ADDRESS,
  SPR_EDIT_TOC_SPARE_PARTS_REQUEST,
} from "../../constants";

export function writeTocSparePartsRequest(sparePartsRequest: any, form: any) {
  let url = "/parts/toc_spare_parts_requests";
  let type = SPR_CREATE_TOC_SPARE_PARTS_REQUEST;
  if (sparePartsRequest.id) {
    url += `/${sparePartsRequest.id}`;
    type = SPR_EDIT_TOC_SPARE_PARTS_REQUEST;
  }
  return {
    type,
    payload: {
      url,
      body: sparePartsRequest,
      form,
    },
  };
}

export function writeSBSparePartsRequest(
  sparePartsRequest: any,
  form: any,
  index?: any
) {
  return {
    type: SPR_CREATE_SB_SPARE_PARTS_REQUEST,
    index,
    payload: {
      url: "/parts/sb_spare_parts_requests",
      body: sparePartsRequest,
      form,
    },
  };
}

export function editSparePartsRequestAddress(
  sparePartsRequest: any,
  form: any
) {
  return {
    type: SPR_EDIT_SPARE_PARTS_REQUEST_ADDRESS,
    payload: {
      url: sparePartsRequest["@id"],
      body: sparePartsRequest,
      form,
    },
  };
}
