import { AnyAction, Reducer } from "redux";
import {
  SUCCESS,
  FAILED,
  HIDE_SUCCESS_ALERT,
  HIDE_ERROR_ALERT,
  PART_EDIT_PART_OBJECT,
  PART_FETCH_ITEMS_MONOLOGISTIC,
} from "../../constants";

interface IPartState {
  showSuccess: boolean;
  showLoading: boolean;
  showError: boolean;
  parts: Array<any>;
  technicianOnCallParts: Array<any>;
  partsListIsLoading?: boolean;
}

let initialState: IPartState = {
  showSuccess: false,
  showLoading: false,
  showError: false,
  parts: [],
  technicianOnCallParts: [],
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.part) {
  initialState = { ...initialState, ...window.SF_INITIAL_STORE_STATE.part };
}

const partReducer: Reducer<IPartState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case PART_EDIT_PART_OBJECT:
      return {
        ...state,
        showLoading: true,
        showSuccess: false,
        showError: false,
      };
    case PART_EDIT_PART_OBJECT + FAILED:
      return {
        ...state,
        showLoading: false,
        showSuccess: false,
        showError: true,
      };
    case PART_EDIT_PART_OBJECT + SUCCESS:
      return {
        ...state,
        showLoading: false,
        showSuccess: true,
        showError: false,
      };
    case `PART_${HIDE_SUCCESS_ALERT}`:
      return {
        ...state,
        showSuccess: false,
      };
    case `PART_${HIDE_ERROR_ALERT}`:
      return {
        ...state,
        showError: false,
      };
    case PART_FETCH_ITEMS_MONOLOGISTIC:
      state = {
        ...state,
        partsListIsLoading: true,
      };
      break;
    case PART_FETCH_ITEMS_MONOLOGISTIC + FAILED:
      state = {
        ...state,
        parts: [],
        partsListIsLoading: false,
      };
      break;
    case PART_FETCH_ITEMS_MONOLOGISTIC + SUCCESS:
      state = {
        ...state,
        parts: action.payload.data["hydra:member"],
        partsListIsLoading: false,
      };
      break;
    default:
      break;
  }
  return state;
};

export default partReducer;
