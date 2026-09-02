import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPagination } from "../@type/IPagination";
import { ISortByIdApiParams } from "../@type/ISortByIdApiParams";
import { ISortByIdApiPayload } from "../@type/ISortByIdApiPayload";
import { setToastMessage } from "../redux/slices/toastSlice";
import { AppDispatch } from "../redux/store";

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export const handleApiError = <P>(error: any) => {
  const response: IBasicApiResponse<P> = {};
  if (error?.status) {
    response.status = error.status;
  }
  if (error?.stage) {
    response.status = 404;
  }
  response.error = error;
  if (error?.status !== 500 && error?.response?.data?.detail) {
    response.errorMessage = error.response.data.detail;
  } else {
    response.errorMessage = "common.error.general_error";
  }
  return response;
};

export const handlePaginationQueryParams = <
  P extends IPagination,
  Q extends IPagination
>(
  data: P,
  queryParams: Q
) => {
  const result = { ...queryParams };
  if (data.itemsPerPage !== undefined) {
    result.itemsPerPage = data.itemsPerPage;
  }
  if (data.page !== undefined) {
    result.page = data.page;
  }
  return result;
};

export const handleSortByIDQueryParams = <
  P extends ISortByIdApiPayload,
  Q extends ISortByIdApiParams
>(
  data: P,
  queryParams: Q
) => {
  const result = { ...queryParams };
  if (data.sortById !== undefined) {
    result["order[id]"] = data.sortById;
  }
  return result;
};

export const toastError = (
  dispatch: AppDispatch,
  response: IBasicApiResponse<unknown>
) => {
  if (response.status === 404) {
    dispatch(setToastMessage("common.error.not_found"));
  } else if (!response.status) {
    dispatch(setToastMessage("common.error.internet_error"));
  } else if (response.status !== 401) {
    dispatch(setToastMessage(response.errorMessage ?? ""));
  }
};
