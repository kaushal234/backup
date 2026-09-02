import { AnyAction, Reducer } from "redux";
import {
  SUCCESS,
  FAILED,
  SPR_FETCH_DELIVERY_ADDRESSES,
  HIDE_SUCCESS_ALERT,
  SPR_CREATE_TOC_SPARE_PARTS_REQUEST,
  SPR_EDIT_TOC_SPARE_PARTS_REQUEST,
  HIDE_ERROR_ALERT,
  SPR_CREATE_SB_SPARE_PARTS_REQUEST,
  SPR_EDIT_SPARE_PARTS_REQUEST_ADDRESS,
} from "../../constants";

interface ISparePartsRequestState {
  sparePartsRequests: Array<any>;
  parts: Array<any>;
  deliveryAddresses: any;
  showSuccess: boolean;
  showLoading: boolean;
  showError: boolean;
  submittedSPR: number;
}

let initialState: ISparePartsRequestState = {
  sparePartsRequests: [],
  parts: [],
  deliveryAddresses: { discr: [] },
  showSuccess: false,
  showLoading: false,
  showError: false,
  submittedSPR: 0,
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.spr) {
  initialState = { ...initialState, ...window.SF_INITIAL_STORE_STATE.spr };
}

const sparePartsRequestReducer: Reducer<ISparePartsRequestState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case SPR_FETCH_DELIVERY_ADDRESSES + SUCCESS: {
      const deliveryAddresses = !state.deliveryAddresses
        ? {}
        : state.deliveryAddresses;
      state = {
        ...state,
        deliveryAddresses: {
          ...deliveryAddresses,
          [action.payload.discriminator]: action.payload.data["hydra:member"],
        },
      };
      break;
    }
    case SPR_CREATE_TOC_SPARE_PARTS_REQUEST:
    case SPR_CREATE_SB_SPARE_PARTS_REQUEST:
    case SPR_EDIT_TOC_SPARE_PARTS_REQUEST:
    case SPR_EDIT_SPARE_PARTS_REQUEST_ADDRESS:
      return {
        ...state,
        showLoading: true,
        showSuccess: false,
        showError: false,
      };
    case SPR_CREATE_TOC_SPARE_PARTS_REQUEST + FAILED:
    case SPR_CREATE_SB_SPARE_PARTS_REQUEST + FAILED:
    case SPR_EDIT_TOC_SPARE_PARTS_REQUEST + FAILED:
    case SPR_EDIT_SPARE_PARTS_REQUEST_ADDRESS + FAILED:
      return {
        ...state,
        showLoading: false,
        showSuccess: false,
        showError: true,
      };
    case SPR_CREATE_TOC_SPARE_PARTS_REQUEST + SUCCESS:
    case SPR_EDIT_TOC_SPARE_PARTS_REQUEST + SUCCESS:
    case SPR_EDIT_SPARE_PARTS_REQUEST_ADDRESS + SUCCESS:
      return {
        ...state,
        showLoading: false,
        showSuccess: true,
        showError: false,
      };
    case SPR_CREATE_SB_SPARE_PARTS_REQUEST + SUCCESS: {
      let showError = false;
      if (state.showError) {
        showError = true;
      }
      return {
        ...state,
        submittedSPR: state.submittedSPR + 1,
        showLoading: false,
        showSuccess: true,
        showError,
      };
    }
    case `SPR_${HIDE_SUCCESS_ALERT}`:
      return {
        ...state,
        showSuccess: false,
      };
    case `SPR_${HIDE_ERROR_ALERT}`:
      return {
        ...state,
        showError: false,
      };
    default:
      break;
  }
  return state;
};

export default sparePartsRequestReducer;
