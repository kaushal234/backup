import Translator from "bazinga-translator";
import sanitize from "sanitize-html";
import Swal from "sweetalert2";
import { unparse } from "papaparse";
import * as XLSX from "xlsx";
import { IPagination } from "../types/IPagination";
import {
  DEFAULT_PAGINATION,
  PROJECT_PHASE_COLORS,
  PROJECT_PHASE_STATUS,
} from "../constants/constants";
import { IDataTableDownloadParams } from "../types/IDataTableDownloadParams";
import { IDataTableHeaderItem } from "../types/IDataTableHeaderItem";
import { IDataTableSavedHeaderItem } from "../types/IDataTableSavedHeaderItem";
import { IGenericFilterFormSubmissionData } from "../types/IGenericFilterFormSubmissionData";
import { IGenericFilterFormData } from "../types/IGenericFilterFormData";
import { IGenericFilterFormValueTypes } from "../types/IGenericFilterFormValueTypes";
import { ISaveDataTableSettingApiPayload } from "../types/ISaveDataTableSettingApiPayload";
import store from "../store";
import { dataTableActions } from "../reducers/dataTable/dataTableSlice";
import { addOrUpdateUserSettingByName } from "../api/addOrUpdateUserSettingByName";
import { IFileDownloadParams } from "../types/IFileDownloadParams";

export const extractDigits = (input: string) => {
  return input.replace(/\D/g, "");
};

export const extractFloat = (input: string): string => {
  let foundDecimal = false;
  return input
    .split("")
    .filter((char) => {
      if (/\d/.test(char)) return true;
      if (char === "." && !foundDecimal) {
        foundDecimal = true;
        return true;
      }
      return false;
    })
    .join("");
};

export const toastSuccess = (message?: string, title?: string) => {
  return Swal.fire({
    icon: "success",
    html: message,
    title: title ?? "Saved",
    confirmButtonText: "OK",
  });
};

export const toastFailure = (message?: string) => {
  return Swal.fire({
    icon: "warning",
    html: message,
    title: "Failed",
    confirmButtonColor: "#DD6B55",
    confirmButtonText: "OK",
  });
};

export const handleError = (error: any) => {
  console.error(error);
  const response = {
    status: 500,
    message: Translator.trans("common.error.server"),
  };
  if (error?.status) {
    response.status = +error.status;
  }
  if (error?.response?.status) {
    response.status = +error.response.status;
  }
  if (response.status !== 500) {
    if (error?.response?.data?.["hydra:description"]) {
      response.message = error.response.data?.["hydra:description"];
    }
    if (error?.response?.data?.detail) {
      response.message = error.response.data.detail;
    }
  }
  if (error?.stage || response.status === 404) {
    response.status = 404;
    response.message = Translator.trans("common.error.not_found");
  }
  if (error?.code === "ECONNABORTED") {
    response.message = Translator.trans("common.error.timeout");
  }
  return response;
};

const decodeEntities = (value: string) => {
  const text = document.createElement("textarea");
  text.innerHTML = value;
  return text.value;
};

export const sanitizeText = (value: string) => {
  let result = value.replace(/<br\s*\/?>/gi, " ");
  result = sanitize(result, {
    allowedTags: [],
    allowedAttributes: {},
  });
  return decodeEntities(result);
};

export const convertToTitleCase = (text: string): string => {
  return text
    .split("_")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
};

export const handlePaginationQueryParams = <
  P extends IPagination,
  Q extends IPagination
>(
  data: P,
  queryParams: Q
) => {
  const result = { ...queryParams };
  result.itemsPerPage = data.itemsPerPage;
  result.page = data.page;
  if (data.pagination) {
    result.pagination = data.pagination;
  }
  return result;
};

export const convertToDateUTCFormat = (dateStr: string) => {
  const date = new Date(dateStr);
  const year = date.getUTCFullYear();
  const month = date.getUTCMonth();
  const day = date.getUTCDate();
  const hour = date.getUTCHours();
  return Date.UTC(year, month, day, hour);
};

export const getProjectPhaseColor = (
  currentStatus: string,
  phaseNumber: number
) => {
  const currentStatusIdx = PROJECT_PHASE_STATUS.findIndex(
    (item) => item === currentStatus
  );

  const phaseNumberIdx = PROJECT_PHASE_STATUS.findIndex(
    (item) => item === `PHASE ${phaseNumber}`
  );

  if (phaseNumberIdx < currentStatusIdx) {
    return "#D9D9D9";
  }
  return PROJECT_PHASE_COLORS[phaseNumber];
};

const formatDataTableContent = (data: IDataTableDownloadParams) => {
  const result: Array<Array<string>> = [];

  let row: Array<string> = [];
  data.headers.forEach((header) => {
    if (header.exportable !== false) {
      row.push(header.value);
    }
  });
  result.push(row);

  data.textRows.forEach((rowData) => {
    row = [];
    rowData.forEach((cell, columnIdx) => {
      if (data.headers[columnIdx]?.exportable !== false) {
        row.push(cell ?? "");
      }
    });
    result.push(row);
  });

  return result;
};

export const downloadCsvFile = (data: IDataTableDownloadParams) => {
  const formattedData = formatDataTableContent(data);
  console.info(formattedData);

  const csv = unparse(formattedData);
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);

  const link = document.createElement("a");
  link.href = url;
  link.download = data.filename;
  link.click();
};

export const downloadXlsxFile = (data: IDataTableDownloadParams) => {
  const formattedData = formatDataTableContent(data);

  const worksheet = XLSX.utils.aoa_to_sheet(formattedData);

  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet1");

  XLSX.writeFile(workbook, `${data.filename}.xlsx`);
};

export const normalizeString = (input: string): string => {
  return input
    .replace(/[^a-zA-Z]+/g, "_")
    .replace(/^_+|_+$/g, "")
    .toLowerCase();
};

export const convertDataTableHeaderToSavedHeader = (
  headers: Array<IDataTableHeaderItem>
): Array<IDataTableSavedHeaderItem> => {
  return headers.map((header) => ({
    value: header.value,
    isHidden: header.isHidden,
    position: header.position,
  }));
};

export const getDataTableName = (name: string) => {
  return `data_table.${normalizeString(name)}`;
};

export const convertFilterSubmissionDataToFormData = (
  values: IGenericFilterFormSubmissionData
) => {
  const result: IGenericFilterFormData = {};

  Object.entries(values).forEach(([key, value]) => {
    if (typeof value === "string") {
      result[key] = value;
    } else if (value instanceof Date) {
      result[key] = value.toISOString();
    } else if (Array.isArray(value)) {
      result[key] = value.map((item) => item.value);
    } else if (value && typeof value === "object") {
      result[key] = value.value;
    }
  });

  return result;
};

export const convertFormValueToFormattedValue = (
  value: IGenericFilterFormValueTypes
) => {
  let formattedValue = "";
  if (typeof value === "string") {
    formattedValue = value;
  } else if (value instanceof Date) {
    formattedValue = value.toLocaleString().slice(0, 10);
  } else if (Array.isArray(value)) {
    formattedValue = value.map((item) => item.label).join(", ");
  } else if (value && typeof value === "object") {
    formattedValue = value.label;
  }
  return formattedValue;
};

export const saveDataTableSettings = async (
  data: ISaveDataTableSettingApiPayload
) => {
  store.dispatch(dataTableActions.setIsLoading(true));
  const state = store.getState();
  const personalization: ISaveDataTableSettingApiPayload = {
    name: getDataTableName(data.name),
    settings: {
      headers: state.dataTable.settings?.headers,
      pagination: data.settings.filters
        ? DEFAULT_PAGINATION
        : state.dataTable.pagination,
      filters: state.dataTable.filters,
      sortModel: state.dataTable.sortModel,
      ...data.settings,
    },
  };
  await addOrUpdateUserSettingByName(personalization);
};

export const mergeDataTableHeaders = (
  headers: Array<IDataTableHeaderItem>,
  savedHeaders: Array<IDataTableSavedHeaderItem>
) => {
  const newHeaders = headers.map((header) => {
    const finalHeader = { ...header };
    const match = savedHeaders.find((item) => item.value === header.value);
    if (match) {
      finalHeader.isHidden = match.isHidden;
      finalHeader.position = match.position;
    }
    return finalHeader;
  });
  return newHeaders;
};

interface IToastConfirmParams {
  message: string;
  title?: string;
}

export async function toastConfirm({
  message,
  title,
}: IToastConfirmParams): Promise<boolean> {
  const result = await Swal.fire({
    title: title ?? Translator.trans("confirmation_modal.title"),
    text: message,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: Translator.trans("confirmation_modal.yes"),
    cancelButtonText: Translator.trans("confirmation_modal.no"),
  });

  return result.isConfirmed;
}

export const delay = (ms: number) => {
  return new Promise((resolve) => {
    setTimeout(resolve, ms);
  });
};

export const hasValidString = (arr: Array<string | undefined>) => {
  return arr.some((str) => (str ?? "").trim() !== "");
};

export const filterStringArray = (arr?: Array<string | undefined>) => {
  if (!arr) return arr;
  return arr.filter(
    (item): item is string => typeof item === "string" && item.trim().length > 0
  );
};

export const castToNumberAsString = (data?: string | number | null) => {
  return !Number.isNaN(data) ? `${+(data ?? "")}` : "";
};

export const decodeHtml = (input: string): string => {
  const textarea = document.createElement("textarea");
  textarea.innerHTML = input;
  const decodedHtml = textarea.value;
  const doc = new DOMParser().parseFromString(decodedHtml, "text/html");
  return doc.body.innerText;
};

export const getFileNameFromPath = (value: string) => {
  const match = value.match(/\d{14}-(.*)$/);
  const filename = match?.[1] ?? "";
  return filename || value;
};

export const downloadFile = (data: IFileDownloadParams) => {
  if (!data.url) return;
  const link = document.createElement("a");
  link.href = data.url;
  link.download = data.fileName;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
