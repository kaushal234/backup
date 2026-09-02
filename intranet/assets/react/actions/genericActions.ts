import {
  SUCCESS,
  FAILED,
  HIDE_SUCCESS_ALERT,
  REDIRECT,
  HIDE_ERROR_ALERT,
} from "../constants";

export function APICallSuccess(type: any, payload: any) {
  return {
    type: type + SUCCESS,
    payload,
  };
}

export function APICallFailed(type: any, payload: any) {
  return {
    type: type + FAILED,
    payload,
  };
}

export function redirect(type: any, code = 404) {
  return {
    type: `${type}_${REDIRECT}_${code}`,
  };
}

export function hideSuccessAlert(type: any) {
  return {
    type: `${type}_${HIDE_SUCCESS_ALERT}`,
  };
}

export function hideErrorAlert(type: any) {
  return {
    type: `${type}_${HIDE_ERROR_ALERT}`,
  };
}
