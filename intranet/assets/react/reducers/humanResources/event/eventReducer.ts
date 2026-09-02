import { AnyAction, Reducer } from "redux";
import {
  SUCCESS,
  EVENT_ADD_EVENT,
  FAILED,
  HIDE_SUCCESS_ALERT,
  HIDE_ERROR_ALERT,
  EVENT_UPDATE_EVENT,
} from "../../../constants";

interface IEventReducer {
  events: Array<any>;
  showSuccess: boolean;
  showError: boolean;
  showLoading: boolean;
  errorMessage: string;
}

let initialState: IEventReducer = {
  events: [],
  showSuccess: false,
  showError: false,
  showLoading: false,
  errorMessage: "",
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.event) {
  initialState = { ...initialState, ...window.SF_INITIAL_STORE_STATE.event };
}

const eventReducer: Reducer<IEventReducer, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case EVENT_ADD_EVENT:
    case EVENT_UPDATE_EVENT:
      return {
        ...state,
        showLoading: true,
        showError: false,
        showSuccess: false,
      };
    case EVENT_ADD_EVENT + SUCCESS:
    case EVENT_UPDATE_EVENT + SUCCESS:
      return {
        ...state,
        showSuccess: true,
        showLoading: false,
        showError: false,
      };
    case EVENT_ADD_EVENT + FAILED:
    case EVENT_UPDATE_EVENT + FAILED:
      return {
        ...state,
        showError: true,
        showSuccess: false,
        showLoading: false,
        errorMessage: action.payload.data["hydra:description"] ?? "",
      };
    case `EVENT_${HIDE_SUCCESS_ALERT}`:
      return {
        ...state,
        showSuccess: false,
        showLoading: false,
        showError: false,
      };
    case `EVENT_${HIDE_ERROR_ALERT}`:
      return {
        ...state,
        showError: false,
        showLoading: false,
        showSuccess: false,
      };
    default:
      break;
  }
  return state;
};

export default eventReducer;
