import Translator from "bazinga-translator";
import { IDropdownItem } from "../types/IDropdownItem";
import { IPagination } from "../types/IPagination";

export const SELECT_OPTIONS: Array<IDropdownItem> = [
  { label: "AVA  - South Marcella", value: "/airports/82" },
  { label: "BDX  - Bordeaux", value: "/airports/61" },
  { label: "BRW  - Port Aidan", value: "/airports/93" },
  { label: "CDG  - Paris", value: "/airports/62" },
  { label: "CNP  - West Zakary", value: "/airports/96" },
  { label: "CYC  - Baumbachview", value: "/airports/66" },
  { label: "DFC  - Hermanland", value: "/airports/91" },
  { label: "DQH  - Schulistborough", value: "/airports/100" },
  { label: "ETM  - Keatonside", value: "/airports/86" },
  { label: "EYJ  - East Alek", value: "/airports/102" },
  { label: "FIY  - East Aric", value: "/airports/80" },
  { label: "FKB  - East Kasandra", value: "/airports/95" },
  { label: "FSB  - Bellside", value: "/airports/107" },
  { label: "GFL  - West Linda", value: "/airports/69" },
  { label: "GRD  - Muflin", value: "/airports/112" },
  { label: "HDF  - South Cletus", value: "/airports/97" },
  { label: "HFT  - West Porter", value: "/airports/72" },
  { label: "IAJ  - Fletcherbury", value: "/airports/78" },
  { label: "IEE  - Haagbury", value: "/airports/83" },
  { label: "IFX  - Lake Collin", value: "/airports/110" },
  { label: "ILS  - Caliland", value: "/airports/89" },
  { label: "KFO  - Jacobsonport", value: "/airports/108" },
  { label: "KLP  - New Beaumouth", value: "/airports/77" },
  { label: "LAK  - Deloresburgh", value: "/airports/90" },
  { label: "LQH  - East Reynafort", value: "/airports/105" },
  { label: "NQU  - Carrollland", value: "/airports/68" },
  { label: "NWU  - Kaliview", value: "/airports/84" },
  { label: "NYS  - East Amparo", value: "/airports/73" },
  { label: "NZA  - Shyannland", value: "/airports/98" },
  { label: "OIN  - East Jena", value: "/airports/104" },
  { label: "OLP  - Kylafort", value: "/airports/74" },
  { label: "PAD  - South Madie", value: "/airports/99" },
  { label: "PLO  - Perpète-les-oies", value: "/airports/111" },
  { label: "QVD  - North Amelyfort", value: "/airports/88" },
  { label: "RHK  - Terenceville", value: "/airports/64" },
  { label: "RPY  - Keenanview", value: "/airports/65" },
  { label: "SDP  - North Ned", value: "/airports/67" },
  { label: "TJW  - Boydport", value: "/airports/87" },
  { label: "TKB  - Modestoside", value: "/airports/76" },
  { label: "TLY  - Ritchieburgh", value: "/airports/71" },
  { label: "TNE  - Reaganville", value: "/airports/103" },
  { label: "UWQ  - Donnellytown", value: "/airports/94" },
  { label: "VCU  - Cruickshankstad", value: "/airports/75" },
  { label: "VOB  - Lake Geraldine", value: "/airports/109" },
  { label: "VXC  - Dareburgh", value: "/airports/92" },
  { label: "WDD  - Okunevafurt", value: "/airports/85" },
  { label: "WWI  - Darbyfort", value: "/airports/101" },
  { label: "XIX  - North Hallefurt", value: "/airports/70" },
  { label: "XVZ  - Ethanland", value: "/airports/81" },
  { label: "YBR  - New Danialfurt", value: "/airports/63" },
  { label: "YIW  - East Courtney", value: "/airports/79" },
  { label: "ZVH  - New Lera", value: "/airports/106" },
];

export const RADIO_OPTIONS: Array<IDropdownItem> = [
  { label: "Yes", value: "yes" },
  { label: "No", value: "no" },
];

export const LANGUAGES_OPTIONS: Array<IDropdownItem> = [
  { value: "AR", label: "Arabic" },
  { value: "BG", label: "Bulgarian" },
  { value: "CS", label: "Czech" },
  { value: "DA", label: "Danish" },
  { value: "DE", label: "German" },
  { value: "EL", label: "Greek" },
  { value: "EN", label: "English" },
  { value: "ES", label: "Spanish" },
  { value: "ET", label: "Estonian" },
  { value: "FI", label: "Finnish" },
  { value: "FR", label: "French" },
  { value: "HU", label: "Hungarian" },
  { value: "ID", label: "Indonesian" },
  { value: "IT", label: "Italian" },
  { value: "JA", label: "Japanese" },
  { value: "KO", label: "Korean" },
  { value: "LT", label: "Lithuanian" },
  { value: "LV", label: "Latvian" },
  { value: "NB", label: "Norwegian (Bokmål)" },
  { value: "NL", label: "Dutch" },
  { value: "PL", label: "Polish" },
  { value: "PT", label: "Portuguese" },
  { value: "RO", label: "Romanian" },
  { value: "RU", label: "Russian" },
  { value: "SK", label: "Slovak" },
  { value: "SL", label: "Slovenian" },
  { value: "SV", label: "Swedish" },
  { value: "TR", label: "Turkish" },
  { value: "UK", label: "Ukrainian" },
  { value: "ZH-HANS", label: "Chinese (Simplified)" },
  { value: "ZH-HANT", label: "Chinese (Traditional)" },
];

export const DOCUMENT_TRANSLATOR_FILE_TYPES =
  "application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream,image/jpeg,image/png,application/vnd.ms-outlook,application/CDFV2-unknown";

export const FORMALITY_OPTIONS: Array<IDropdownItem> = [
  { value: "default", label: "Default" },
  {
    value: "prefer_more",
    label: "Prefer More",
    tooltip: "global_chat.formality.tooltip.prefer_more",
  },
  {
    value: "prefer_less",
    label: "Prefer Less",
    tooltip: "global_chat.formality.tooltip.prefer_less",
  },
];

export const CONTRACT_RENEWAL_UNIT_OPTIONS: Array<IDropdownItem> = [
  { label: "Day", value: "DAY" },
  { label: "Month", value: "MONTH" },
  { label: "Year", value: "YEAR" },
];

export const CONTRACT_STATUS_OPTIONS: Array<IDropdownItem> = [
  { label: "Active", value: "ACTIVE" },
  { label: "Expired", value: "EXPIRED" },
  { label: "Archived", value: "ARCHIVED" },
];

export const PROJECT_PHASE_COLORS = [
  "#FFFFA5",
  "#ADC7E9",
  "#5E91D3",
  "#DFF1D3",
  "#9FD77F",
];

export const PROJECT_PHASE_STATUS = [
  "PENDING",
  "PHASE 0",
  "PHASE 1",
  "PHASE 2",
  "PHASE 3",
  "PHASE 4",
  "CANCELLED",
  "CLOSED",
];

export const PROJECT_IFACTOR_VALUES: Array<IDropdownItem> = [
  { label: "IF 1", value: "IF 1" },
  { label: "IF 10", value: "IF 10" },
  { label: "IF 100", value: "IF 100" },
  { label: "IF 1000", value: "IF 1000" },
  { label: "IF 10000", value: "IF 10000" },
];

export const AIRCRAFT_COMPATIBILITY_FILE_TYPE_OPTIONS: Array<IDropdownItem> = [
  { value: "NTO", label: "NTO" },
  { value: "SIL", label: "SIL" },
  { value: "OTHER", label: "OTHER" },
];

export const SORT_OPTION = { ASC: "ASC", DESC: "DESC" } as const;

export const PAGE_SIZE_OPTIONS = [10, 25, 50, 100];

export const DOWNLOAD_FILE_TYPE = {
  csv: "csv",
  xlsx: "xlsx",
} as const;

export const DOWNLOAD_FILE_STRATEGY = {
  all: "all",
  filtered: "filtered",
} as const;

export const DATA_TABLE_DOWNLOAD_OPTIONS: Array<IDropdownItem> = [
  { label: "CSV", value: DOWNLOAD_FILE_TYPE.csv },
  { label: "XLSX", value: DOWNLOAD_FILE_TYPE.xlsx },
];

export const DATA_TABLE_DOWNLOAD_STRATEGY: Array<IDropdownItem> = [
  { label: "All Data", value: DOWNLOAD_FILE_STRATEGY.all },
  { label: "Filtered Data", value: DOWNLOAD_FILE_STRATEGY.filtered },
];

export const DEFAULT_PAGINATION: IPagination = {
  page: 0,
  itemsPerPage: PAGE_SIZE_OPTIONS[0],
};

export const CONTACT_CAMPAIGN_STATUS_OPTIONS: Array<IDropdownItem> = [
  { value: "DRAFT", label: "Draft" },
  { value: "ACTIVE", label: "Active" },
  { value: "CLOSED", label: "Closed" },
];

export const CONTRACT_CUSTOMERS_SUB_CATEGORIES: Array<string> = [
  "/contract/sub_categories/25",
  "/contract/sub_categories/26",
  "/contract/sub_categories/27",
  "/contract/sub_categories/28",
  "/contract/sub_categories/29",
  "/contract/sub_categories/30",
  "/contract/sub_categories/31",
  "/contract/sub_categories/32",
  "/contract/sub_categories/33",
];

export const CONTRACT_AI_EXTERNAL_PARTY_TYPES = ["client", "supplier"];

export const COMMENT_TYPES = {
  internalWithNotification: "INTERNAL_WITH_NOTIFICATION",
  internal: "INTERNAL",
  external: "EXTERNAL",
};

export const COMMENT_TYPES_RADIO_OPTIONS: Array<IDropdownItem> = [
  {
    value: COMMENT_TYPES.internalWithNotification,
    label: Translator.trans("toc.comment.type.internal_with_notification"),
  },
  {
    value: COMMENT_TYPES.internal,
    label: Translator.trans("toc.comment.type.internal"),
  },
  {
    value: COMMENT_TYPES.external,
    label: Translator.trans("toc.comment.type.external"),
  },
];

export const CONFIDENTIAL_COMMENT_TYPES_RADIO_OPTIONS: Array<IDropdownItem> = [
  {
    value: COMMENT_TYPES.internalWithNotification,
    label: Translator.trans("toc.comment.type.internal_with_notification"),
  },
  {
    value: COMMENT_TYPES.internal,
    label: Translator.trans("toc.comment.type.internal"),
  },
];

export const DATE_TIME_FORMAT_LONG = "MMMM D, YYYY [at] h:mm A";

export const IFACTOR_OPTIONS: Array<IDropdownItem> = [
  { label: "IF 1", value: "IF 1" },
  { label: "IF 10", value: "IF 10" },
  { label: "IF 100", value: "IF 100" },
  { label: "IF 1000", value: "IF 1000" },
];

export const TASK_IFACTOR_OPTIONS: Array<IDropdownItem> = [
  ...IFACTOR_OPTIONS,
  { label: "IF 10000", value: "IF 10000" },
];

export const TOC_FILTER_TAG_MAP: { [key: string]: string } = {
  ibs: "Involves iBS",
  ihs: "Involves LINK",
  link: "Involves iHS/ipHS",
  apu_off: "Involves APU-OFF",
};

export const ACCEPT_FILES = {
  comment:
    "application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/octet-stream, image/jpeg, image/png, application/vnd.ms-outlook, application/CDFV2-unknown, application/zip, application/x-zip-compressed, application/vnd.openxmlformats-officedocument.presentationml.presentation, application/vnd.ms-powerpoint, video/mp4",
} as const;

export const FACTORY_FLAG = {
  open: "OPEN_FACTORY_FLAG",
  close: "CLOSE_FACTORY_FLAG",
} as const;
