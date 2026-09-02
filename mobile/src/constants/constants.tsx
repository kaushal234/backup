import sanitizeHtml, { IOptions } from "sanitize-html";
import { IDropdownItem } from "../@type/IDropdownItem";
import { SW_VERSION } from "./version";

export const APP_VERSION = `v1.0.${SW_VERSION}`;

export const FAKE_TOKEN =
  "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3MzU1NDgwNDksImV4cCI6MTczNTYzNDQ0OSwicm9sZXMiOlsiUk9MRV9QQVNTV09SRF9OT1RfRVhQSVJFRCJdLCJ1c2VybmFtZSI6ImFudGhvbnkuZmFjaGF1eEBhbHZlc3QuZnIiLCJwb3J0YWwiOiJpbnRyYW5ldCIsIkBpZCI6Ii9wZW9wbGUvMTkxNTQiLCJAdHlwZSI6IlBlb3BsZSIsImJ1c2luZXNzVW5pdCI6eyJAaWQiOiIvYnVzaW5lc3NfdW5pdHMvNSIsIkB0eXBlIjoiQnVzaW5lc3NVbml0IiwibGVnYWN5SWQiOjUsImlkIjo1LCJuYW1lIjoiQUxWRVNUIn0sImhpZGRlbiI6ZmFsc2UsImRpc2FibGVkIjpmYWxzZSwibGVnYWN5SWQiOjY2MjAsInBob3RvIjp7IkBpZCI6Ii9wZW9wbGVfZmlsZXMvNjQ3OTQ0NiIsIkB0eXBlIjoiUGVvcGxlRmlsZSIsImlkIjo2NDc5NDQ2LCJmaWxlUGF0aCI6InBob3Rvcy9hbnRob255ZmFjaGF1eGFsdmVzdGZyLWIwNTdlMGUuanBnIiwiY3JlYXRlZEF0IjoiMjAyNC0wMS0xN1QxMTozMzoxNi0wNTowMCJ9LCJmaXJzdG5hbWUiOiJBbnRob255IiwibGFzdG5hbWUiOiJGQUNIQVVYIiwiaWQiOjE5MTU0LCJwYXNzd29yZEV4cGlyYXRpb25EYXRlIjoiMjAyNS0wMS0wN1QwMTo0MToyMC0wNTowMCIsImFjbHMiOlsiQUNMX0FVVEhfSU5UUkFORVQiLCJBQ0xfQVVUSF9JTlRSQU5FVF81IiwiR0dfMVlfRVRISUNfQVRURVNUQVRJT04iLCJHR18xWV9FVEhJQ19BVFRFU1RBVElPTl81IiwiR0dfQURNSU4iLCJHR19BRE1JTl81IiwiR0dfSEVMUERFU0tfQURNSU4iLCJHR19IRUxQREVTS19BRE1JTl81IiwiR0dfTElOS19VU0VSIiwiR0dfTElOS19VU0VSXzUiLCJHR19NSVMiLCJHR19NSVNfNjMiLCJHR19NSVNfU0FHRSIsIkdHX01JU19TQUdFXzUiLCJHR19TVEFHSU5HIiwiR0dfU1RBR0lOR181IiwiUElfT1BFUkFUT1IiLCJQSV9PUEVSQVRPUl82MyIsInBpX1BJTE9UX1BJTy1URVNUIiwicGlfUElMT1RfUElPLVRFU1RfNjMiLCJQSV9URVNURVIiLCJQSV9URVNURVJfNjMiLCJST0xFX0RFViIsIlJPTEVfREVWXzUiLCJST0xFX0VBIiwiUk9MRV9FQV81IiwiUk9MRV9FUlAiLCJST0xFX0VSUF81IiwiUk9MRV9NSVNNIiwiUk9MRV9NSVNNXzUiLCJST0xFX05BIiwiUk9MRV9OQV81IiwiU1VQRVJVU0VSIiwiU1VQRVJVU0VSXzYzIl19.G8sQJc2HssdTGlgJjg3tiga59sQG5Pjz5P6MGdVtUWFfETzUiTb1oqhyc2jBJXndole4K1JFaCz2gNhZoaH7E76h2YAmy0tWeZ_TI08KliOVsNuCEdZYRAp6p13d6U8mOvglet0yWQO7XDBPQdI0Vo-kbIR_uIVPP7wl-9nk7SQX6HGrQ37PnqsqxBYiNqdDgvbr-BOohRllEBzM-Od0o8KYtMKaoR2ZskBLhLB_g2AxRT93pLLktErPLKm1-uBjM0rLH2pGAlp2QJkr7QWMmniAUdWGvkvkp_P3pARtWHCtm7La1bQHQt76d13bV3StXR4JfGfPJHQtiTxOHwDq2B4JAujLdw9ZeKcwX5PlKL6QdVbGGJJt3F43xmWqotGkwq67UQpWtrJbIOh8HAPj2egWeocwZ6j7vql99j8xylFmGFAWZAUtKYw8w7t2_DBJH8Hc7-0EWkP8_gQR4P_erS9GRiltGmVVfk-MuFroNqGPJfqUXFlk-9m_cnZYmLYlISoS1WtH-dt_GEZC7hGPelghtp7QA2G79L1P64MAYiilmuAbRGnWwKsmSn42JFL8WWQeEb0kGTcp_AdtnQNVkVhpnPw7DoPl113N3QRqh7KaWUhrAjpgauh1C6_jIwpzlbtG4zqlG6QGdRsB4DuagLKswXfh1Y7B-UFGpKnyu34";

export const LANGUAGES = ["en", "fr", "zh"];

export const LANGUAGE_FILE_NAMES = ["custom"];

export const IDB_DATABASE = {
  name: "main_store",
  stores: {
    user_info: "user_info",
    url_info: "url_info",
    sync_post_comment: "sync_post_comment",
    sync_post_toc_files: "sync_post_toc_files",
    sync_post_toc_and_files: "sync_post_toc_and_files",
    sync_post_csr_file: "sync_post_csr_file",
    sync_put_toc: "sync_put_toc",
    sync_delete_toc: "sync_delete_toc",
    sync_put_toc_status: "sync_put_toc_status",
    sync_put_request_technician: "sync_put_request_technician",
    sync_post_toc_parts: "sync_post_toc_parts",
    sync_put_toc_parts: "sync_put_toc_parts",
    sync_delete_toc_parts: "sync_delete_toc_parts",
    sync_delete_toc_file: "sync_delete_toc_file",
    sync_put_csr: "sync_put_csr",
    sync_put_intervention: "sync_put_intervention",
    sync_post_csr_hour_meter: "sync_post_csr_hour_meter",
    sync_post_toc_spr: "sync_post_toc_spr",
    sync_post_extranet_user: "sync_post_extranet_user",
  },
} as const;

export const IDB_DATABASE_VERSION = SW_VERSION;

export const DATE_FORMAT = "YYYY-MM-DD";

export const DATE_TIME_FORMAT_LONG = "MMMM D, YYYY [at] h:mm A";

export const DATE_TIME_FORMAT = "YYYY-MM-DD h:mm A";

export const DATE_TODAY = "TODAY";

export const TOC_FILTER_STATUS_MAP: { [key: string]: string } = {
  PENDING: "Pending",
  IN_PROGRESS: "In Progress",
  SUSPENDED: "Suspended",
  SOLVED: "Solved",
  CLOSED: "Closed",
};

export const CSR_FILTER_STATUS_MAP: { [key: string]: string } = {
  PENDING: "Pending",
  PLANNED: "Planned",
  ASSIGNED: "Assigned",
  "IN-PROGRESS": "In Progress",
  COMPLETED: "Completed",
  CLOSED: "Closed",
};

export const CSR_FILTER_TYPE_MAP: { [key: string]: string } = {
  default: "Default",
  toc: "TOC",
  sb: "Service Bulletin",
  commissioning: "Commissioning",
};

export const TOC_FILTER_IFACTOR_OPTIONS: Array<IDropdownItem> = [
  {
    id: "IF 1",
    text: "IF 1",
    infoText: "common.ifactor.if_1",
  },
  {
    id: "IF 10",
    text: "IF 10",
    infoText: "common.ifactor.if_10",
  },
  {
    id: "IF 100",
    text: "IF 100",
    infoText: "common.ifactor.if_100",
  },
  {
    id: "IF 1000",
    text: "IF 1000",
    infoText: "common.ifactor.if_1000",
  },
];

export const BOOLEAN_OPTIONS: Array<IDropdownItem> = [
  {
    id: "1",
    text: "Yes",
  },
  {
    id: "0",
    text: "No",
  },
];

export const TOC_IFACTOR = {
  IF_1: "IF 1",
  IF_10: "IF 10",
  IF_100: "IF 100",
  IF_1000: "IF 1000",
} as const;

export const UNIT_OPERATIONAL_STATUS = {
  MCF: "/unit_operational_statuses/MCF",
} as const;

export const CSR_FILTER_DISCRIMINATOR = [
  { id: "default", text: "Default" },
  { id: "toc", text: "TOC" },
  { id: "sb", text: "Service Bulletin" },
  { id: "commissioning", text: "Commissioning" },
];

export const TOC_FILTER_TAG_MAP: { [key: string]: string } = {
  ibs: "Involves iBS",
  ihs: "Involves LINK",
  link: "Involves iHS/ipHS",
  apu_off: "Involves APU-OFF",
};

export const SORT_OPTION = { ASC: "ASC", DESC: "DESC" } as const;

export const AVATAR_BG_COLORS = [
  "#f44336",
  "#9c27b0",
  "#673ab7",
  "#3f51b5",
  "#2196f3",
  "#03a9f4",
  "#00bcd4",
  "#009688",
  "#4caf50",
  "#8bc34a",
  "#cddc39",
  "#ffeb3b",
  "#ffc107",
  "#ff9800",
  "#ff5722",
  "#795548",
  "#9e9e9e",
  "#607d8b",
];

export const MAX_FILE_SIZE = 5 * 1024 * 1024;

export const ACCEPT_FILES = {
  image: "image/*",
  pdf: "application/pdf",
  excel: ".xlsx,.xls",
  word: ".docx,.doc",
  file: "application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/octet-stream, image/jpeg, image/png, application/vnd.ms-outlook, application/CDFV2-unknown",
  comment:
    "application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/octet-stream, image/jpeg, image/png, application/vnd.ms-outlook, application/CDFV2-unknown, application/zip, application/x-zip-compressed, application/vnd.openxmlformats-officedocument.presentationml.presentation, application/vnd.ms-powerpoint, video/mp4, video/webm, video/x-matroska",
} as const;

export const LS_KEYS = {
  toc_filters: "toc.filters",
  er_filters: "er.filters",
  csr_filters: "csr.filters",
};

export const PAGE_TYPES = {
  CSR: "CSR",
  TOC: "TOC",
} as const;

export const COMMENT_TYPES = {
  CSR: "CSR",
  TOC: "TOC",
  TOC_FROM_CSR: "TOC_FROM_CSR",
} as const;

export const FETCH_FILE_TYPES = {
  CsrFile: "CsrFile",
  TocFile: "TocFile",
  CommentFile: "CommentFile",
  TocMainFile: "TocMainFile",
} as const;

export const CACHE_STATIC_NAME = `static-v${SW_VERSION}`;

export const CACHE_DYNAMIC_NAME = `dynamic-v${SW_VERSION}`;

export const LOCAL_STORAGE = {
  userInfo: "userInfo",
};

export const TOC_STATUS_LIST = [
  {
    title: "Pending",
    value: "PENDING",
  },
  {
    title: "In Progress",
    value: "IN_PROGRESS",
  },
  {
    title: "Suspended",
    value: "SUSPENDED",
  },
  {
    title: "Solved",
    value: "SOLVED",
  },
  {
    title: "Closed",
    value: "CLOSED",
  },
];

export const CSR_STATUS_LIST = [
  {
    title: "Pending",
    value: "PENDING",
    progress: 20,
  },
  {
    title: "Planned",
    value: "PLANNED",
    progress: 40,
  },
  {
    title: "Assigned",
    value: "ASSIGNED",
    progress: 60,
  },
  {
    title: "In Progress",
    value: "IN-PROGRESS",
    progress: 80,
  },
  {
    title: "Completed",
    value: "COMPLETED",
    progress: 90,
  },
  {
    title: "Closed",
    value: "CLOSED",
    progress: 100,
  },
];

export const TOC_STATUS = {
  PENDING: "PENDING",
  IN_PROGRESS: "IN_PROGRESS",
  SUSPENDED: "SUSPENDED",
  SOLVED: "SOLVED",
  CLOSED: "CLOSED",
};

export const FACTORY_FLAG = {
  open: "OPEN_FACTORY_FLAG",
  close: "CLOSE_FACTORY_FLAG",
} as const;

export const TOC_TABS = {
  parts: "parts",
} as const;

export const CSR_STATUS = {
  PENDING: "PENDING",
  ASSIGNED: "ASSIGNED",
  "IN-PROGRESS": "IN-PROGRESS",
  COMPLETED: "COMPLETED",
  CLOSED: "CLOSED",
  PLANNED: "PLANNED",
};

export const INTERVENTION_STATUS = {
  PENDING: "PENDING",
  FAILED_ASSIGNEE: "FAILED_ASSIGNEE",
  STARTED: "STARTED",
  SOLVED: "SOLVED",
  TO_CONTINUE: "TO_CONTINUE",
};

export const INTERVENTION_STATUS_MAP: { [key: string]: string } = {
  PENDING: "Pending",
  FAILED_ASSIGNEE: "Failed Assignee",
  STARTED: "Started",
  SOLVED: "Solved",
  TO_CONTINUE: "To Continue",
};

export const SANITIZE_HTML_OPTIONS: IOptions = {
  allowedAttributes: {
    ...sanitizeHtml.defaults.allowedAttributes,
    i: ["class"],
  },
};

export const SANITIZE_ALL: IOptions = {
  allowedTags: [],
  allowedAttributes: {},
};

export const TOC_PARTS_REPLACEMENT = {
  supplier: "SUPPLIER",
  customer: "CUSTOMER",
  quotation: "QUOTATION",
};

export const REPLACEMENT_OPTIONS: Array<IDropdownItem> = [
  { id: "SUPPLIER", text: "toc_parts.table.supplier_replaces" },
  { id: "CUSTOMER", text: "toc_parts.table.customer_replaces" },
  { id: "QUOTATION", text: "toc_parts.table.quotation_required" },
];

export const CSR_TYPE = {
  commissioning: "commissioning",
};

export const SPR_ADDRESS = {
  existing: "EXISTING",
  new: "NEW",
};

export const SPR_ADDRESS_OPTIONS: Array<IDropdownItem> = [
  {
    id: SPR_ADDRESS.existing,
    text: "toc_spr_form.address.type.options.existing",
  },
  {
    id: SPR_ADDRESS.new,
    text: "toc_spr_form.address.type.options.new",
  },
];

export const LANGUAGES_OPTIONS: Array<IDropdownItem> = [
  { id: "AR", text: "Arabic" },
  { id: "BG", text: "Bulgarian" },
  { id: "CS", text: "Czech" },
  { id: "DA", text: "Danish" },
  { id: "DE", text: "German" },
  { id: "EL", text: "Greek" },
  { id: "EN", text: "English" },
  { id: "ES", text: "Spanish" },
  { id: "ET", text: "Estonian" },
  { id: "FI", text: "Finnish" },
  { id: "FR", text: "French" },
  { id: "HU", text: "Hungarian" },
  { id: "ID", text: "Indonesian" },
  { id: "IT", text: "Italian" },
  { id: "JA", text: "Japanese" },
  { id: "KO", text: "Korean" },
  { id: "LT", text: "Lithuanian" },
  { id: "LV", text: "Latvian" },
  { id: "NB", text: "Norwegian (Bokmål)" },
  { id: "NL", text: "Dutch" },
  { id: "PL", text: "Polish" },
  { id: "PT", text: "Portuguese" },
  { id: "RO", text: "Romanian" },
  { id: "RU", text: "Russian" },
  { id: "SK", text: "Slovak" },
  { id: "SL", text: "Slovenian" },
  { id: "SV", text: "Swedish" },
  { id: "TR", text: "Turkish" },
  { id: "UK", text: "Ukrainian" },
  { id: "ZH", text: "Chinese" },
];

export const PHONE_FORMAT = /^\+\d{1,3}[1-9]\d{6,14}$/;

export const TOC_STATUS_FILTER_DEFAULT: Array<IDropdownItem> = [
  {
    id: "IN_PROGRESS",
    text: "In Progress",
  },
  {
    id: "PENDING",
    text: "Pending",
  },
  {
    id: "SUSPENDED",
    text: "Suspended",
  },
];

export const TOC_STATUS_FILTER_OTHERS: Array<IDropdownItem> = [
  {
    id: "SOLVED",
    text: "Solved",
  },
  {
    id: "CLOSED",
    text: "Closed",
  },
];

export const TOC_STATUS_FILTERS: Array<IDropdownItem> = [
  ...TOC_STATUS_FILTER_DEFAULT,
  ...TOC_STATUS_FILTER_OTHERS,
];

export const CSR_STATUS_FILTER_DEFAULT: Array<IDropdownItem> = [
  {
    id: "ASSIGNED",
    text: "Assigned",
  },
  {
    id: "COMPLETED",
    text: "Completed",
  },
  {
    id: "IN-PROGRESS",
    text: "In Progress",
  },
  {
    id: "PENDING",
    text: "Pending",
  },
  {
    id: "PLANNED",
    text: "Planned",
  },
];

export const TOC_CREATE_PARAMS = {
  erSerialNumber: "erSerialNumber",
} as const;

export const TOC_SERVICE_ACTIVITY = {
  COMMISSIONING: "/service/service_activities/2",
  INFO_REQUEST: "/service/service_activities/7",
};

export const TOC_UNIT_OPERATIONAL_STATUS_MCF: IDropdownItem = {
  id: "/unit_operational_statuses/MCF",
  text: "Mission Capable Fully - MCF",
};
