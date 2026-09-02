import { API_METHOD } from "./constants";

export const API_CALL = {
  getTocList: {
    method: API_METHOD.GET,
    name: "getTocList",
    url: /\/service\/technician_on_calls(\?.*)$/,
  },
  postToc: {
    method: API_METHOD.POST,
    name: "postToc",
    url: /\/service\/technician_on_calls$/,
  },
  putToc: {
    method: API_METHOD.PUT,
    name: "putToc",
    url: /\/service\/technician_on_calls\/\d+$/,
  },
  postComment: {
    method: API_METHOD.POST,
    name: "postComment",
    url: /\/comments/,
  },
  getComments: {
    method: API_METHOD.GET,
    name: "getComments",
    url: /\/comments/,
  },
  postTocFile: {
    method: API_METHOD.POST,
    name: "postTocFile",
    url: /\/service\/technician_on_calls\/\d+\/files/,
  },
  postCsrFile: {
    method: API_METHOD.POST,
    name: "postCsrFile",
    url: /\/service\/customer_service_records\/\d+\/files/,
  },
  getCsrList: {
    method: API_METHOD.GET,
    name: "getCsrList",
    url: /\/service\/customer_service_records(\?.*)$/,
  },
  getErList: {
    method: API_METHOD.GET,
    name: "getErList",
    url: /\/equipment_records(\?.*)$/,
  },
  downloadManual: {
    method: API_METHOD.GET,
    name: "downloadManual",
    url: /\/support\/manual_documents\/\d+\/pdf\/document/,
  },
  downloadSchematic: {
    method: API_METHOD.GET,
    name: "downloadSchematic",
    url: /\/ion\/bill-of-materials\/drawings\/site=500;project=/,
  },
  deleteTocPart: {
    method: API_METHOD.DELETE,
    name: "deleteTocPart",
    url: /\/service\/technician_on_call_parts\/\d+$/,
  },
  postTocPart: {
    method: API_METHOD.POST,
    name: "postTocPart",
    url: /\/service\/technician_on_call_parts$/,
  },
  putTocPart: {
    method: API_METHOD.PUT,
    name: "putTocPart",
    url: /\/service\/technician_on_call_parts\/\d+$/,
  },
  getUnitOperationalStatusList: {
    method: API_METHOD.GET,
    name: "getUnitOperationalStatusList",
    url: /\/unit_operational_statuses$/,
  },
  deleteToc: {
    method: API_METHOD.DELETE,
    name: "deleteTocPart",
    url: /\/service\/technician_on_calls\/\d+$/,
  },
  postTocSpr: {
    method: API_METHOD.POST,
    name: "postTocPart",
    url: /\/parts\/toc_spare_parts_requests$/,
  },
  postTocMainFile: {
    method: API_METHOD.POST,
    name: "postTocMainFile",
    url: /\/service\/technician_on_calls\/\d+\/main_file/,
  },
  deleteTocFile: {
    method: API_METHOD.DELETE,
    name: "deleteTocFile",
    url: /\/service\/technician_on_calls\/\d+\/files/,
  },
  postExtranetUser: {
    method: API_METHOD.POST,
    name: "postExtranetUser",
    url: /\/sales\/extranet_users\/with_crt_and_group$/,
  },
} as const;
