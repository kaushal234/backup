import { AnyAction, Reducer } from "redux";
import {
  SUCCESS,
  FAILED,
  HIDE_SUCCESS_ALERT,
  HIDE_ERROR_ALERT,
  SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM,
  SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM,
  SALES_CRT_FETCH_CUSTOMER,
} from "../../constants";

interface ICustomerRelationshipTeamState {
  customerRelationshipTeams: Array<any>;
  selectedCustomer: any;
  crtCreated: boolean;
  showSuccess: boolean;
  showError: boolean;
  showLoading: boolean;
  errorMessage: string;
  details?: any;
}

let initialState: ICustomerRelationshipTeamState = {
  customerRelationshipTeams: [],
  selectedCustomer: null,
  crtCreated: false,
  showSuccess: false,
  showError: false,
  showLoading: false,
  errorMessage: "",
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.crt) {
  initialState = { ...initialState, ...window.SF_INITIAL_STORE_STATE.crt };
}

const customerRelationshipTeamReducer: Reducer<
  ICustomerRelationshipTeamState,
  AnyAction
  // eslint-disable-next-line default-param-last
> = (state = initialState, action) => {
  switch (action.type) {
    case SALES_CRT_FETCH_CUSTOMER + SUCCESS:
      return {
        ...state,
        selectedCustomer: action.payload.data,
      };
    case SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM:
    case SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM:
      return {
        ...state,
        showLoading: true,
        crtCreated: false,
        showError: false,
      };
    case SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM + SUCCESS:
      return {
        ...state,
        showLoading: false,
        crtCreated: true,
        showError: false,
      };
    case SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM + SUCCESS:
      return {
        ...state,
        showLoading: false,
        showError: false,
        showSuccess: true,
      };
    case SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM + FAILED:
    case SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM + FAILED:
      return {
        ...state,
        showLoading: false,
        crtCreated: false,
        showError: true,
        errorMessage: action.payload.data["hydra:description"] ?? "",
      };
    case `CRT_${HIDE_SUCCESS_ALERT}`:
      return {
        ...state,
        showSuccess: false,
      };
    case `CRT_${HIDE_ERROR_ALERT}`:
      return {
        ...state,
        showError: false,
      };
    default:
      break;
  }
  return state;
};

export default customerRelationshipTeamReducer;
