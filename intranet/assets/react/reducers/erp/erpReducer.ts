import { AnyAction, Reducer } from "redux";
import {
  SUCCESS,
  FAILED,
  ERP_FETCH_BUSINESS_PARTNERS,
  ERP_FETCH_BUSINESS_PARTNER,
  ERP_FETCH_ITEMS,
  ERP_FETCH_SUPPLIERS,
} from "../../constants";

interface IErpState {
  customer: any;
  businessPartner: any;
  businessPartners: Array<any>;
  parts: Array<any>;
  partsListIsLoading?: boolean;
  businessPartnersListIsLoading?: boolean;
  customers?: any;
}

let initialState: IErpState = {
  customer: {},
  businessPartner: {},
  businessPartners: [],
  parts: [],
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.erp) {
  initialState = { ...initialState, ...window.SF_INITIAL_STORE_STATE.erp };
}

const erpReducer: Reducer<IErpState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case ERP_FETCH_BUSINESS_PARTNER + SUCCESS:
      return {
        ...state,
        businessPartner: action.payload.data,
      };
    case ERP_FETCH_BUSINESS_PARTNER + FAILED:
      return {
        ...state,
        businessPartner: {},
      };
    case ERP_FETCH_BUSINESS_PARTNERS:
    case ERP_FETCH_SUPPLIERS:
      return {
        ...state,
        businessPartnersListIsLoading: true,
      };
    case ERP_FETCH_BUSINESS_PARTNERS + SUCCESS:
    case ERP_FETCH_SUPPLIERS + SUCCESS:
      return {
        ...state,
        businessPartners: action.payload.data["hydra:member"],
        businessPartnersListIsLoading: false,
      };
    case ERP_FETCH_BUSINESS_PARTNERS + FAILED:
    case ERP_FETCH_SUPPLIERS + FAILED:
      return {
        ...state,
        businessPartners: [],
        businessPartnersListIsLoading: false,
      };
    case ERP_FETCH_ITEMS:
      state = {
        ...state,
        partsListIsLoading: true,
      };
      break;
    case ERP_FETCH_ITEMS + FAILED:
      state = {
        ...state,
        parts: [],
        partsListIsLoading: false,
      };
      break;
    case ERP_FETCH_ITEMS + SUCCESS:
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

export default erpReducer;
