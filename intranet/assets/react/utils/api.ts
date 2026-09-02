import axios from "axios";
import { CANCEL } from "redux-saga";
import _ from "lodash";
import Translator from "bazinga-translator";
import { client } from "../store";
import { IBasicApiResponse } from "../types/IBasicApiResponse";

export function fetchAPI(url: any, config = {}) {
  const source = axios.CancelToken.source();
  const request: any = client.get(url, {
    ...config,
    cancelToken: source.token,
  });
  request[CANCEL] = () => source.cancel();
  return request;
}

export function generateFormErrors(error: any) {
  const errors: any = {};
  if (!error.response || !error.response.data || !error.response.status) {
    errors._error = error.toString();
  } else if (
    (error.response.status === 400 || error.response.status === 422) &&
    _.has(error.response, "data.violations")
  ) {
    error.response.data.violations.forEach((violation: any) => {
      _.set(errors, violation.propertyPath, violation.message);
    });
  } else {
    errors._error =
      error.response.data["hydra:description"] ||
      error.response.data.message ||
      "Internal Server Error.";
  }

  return errors;
}

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
    response.errorMessage = Translator.trans("common.error.server");
  }
  return response;
};
