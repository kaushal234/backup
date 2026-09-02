import { AnyAction, Reducer } from "redux";
import {
  MIS_FETCH_MODULES,
  MIS_FETCH_TYPES,
  SUCCESS,
  MIS_UPDATE_TROUBLE_TICKET,
  FAILED,
  HIDE_SUCCESS_ALERT,
  HIDE_ERROR_ALERT,
  MIS_UPDATE_TROUBLE_TICKET_OWNERS,
  MIS_GET_TROUBLE_TICKETS_BY_MODULE,
  MIS_ADD_TROUBLE_TICKETS_TO_USER_STORIES,
  MIS_TROUBLE_TICKETS_RESET,
  MIS_FETCH_TAGS,
  MIS_UPDATE_SUPPORT_TEAM,
  MIS_CREATE_SUPPORT_TEAM,
} from "../../constants";

export interface IMisState {
  types: Array<any>;
  modules: Array<any>;
  tags: Array<any>;
  troubleTickets: Array<any>;
  troubleTicketsByModule: any;
  troubleTicketsRefresh: boolean;
  showSuccess: boolean;
  showError: boolean;
  showLoading: boolean;
  showFiles: boolean;
  isLoading: boolean;
  details: Array<any>;
  errorMessage?: any;
  showConfirmation?: any;
  applications?: any;
}

let initialState: IMisState = {
  types: [],
  modules: [],
  tags: [],
  troubleTickets: [],
  troubleTicketsByModule: {},
  troubleTicketsRefresh: false,
  showSuccess: false,
  showError: false,
  showLoading: false,
  showFiles: false,
  isLoading: true,
  details: [],
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.mis) {
  initialState = { ...initialState, ...window.SF_INITIAL_STORE_STATE.mis };
}

const misReducer: Reducer<IMisState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  const modules: any = {};
  const tags: any = {};
  const types: any = {};
  const troubleTickets: any = {};
  switch (action.type) {
    case MIS_FETCH_MODULES:
    case MIS_FETCH_TAGS:
      return {
        ...state,
      };
    case MIS_FETCH_MODULES + SUCCESS:
      action.payload.data["hydra:member"].forEach((module: any) => {
        modules[module["@id"]] = module;
      });
      return {
        ...state,
        modules,
      };
    case MIS_FETCH_TAGS + SUCCESS:
      action.payload.data["hydra:member"].forEach((tag: any) => {
        tags[tag["@id"]] = tag;
      });
      return {
        ...state,
        tags,
      };
    case MIS_FETCH_TYPES:
      return {
        ...state,
      };
    case MIS_FETCH_TYPES + SUCCESS:
      action.payload.data["hydra:member"].forEach((type: any) => {
        types[type["@id"]] = type;
      });
      return {
        ...state,
        types,
      };
    case MIS_UPDATE_TROUBLE_TICKET_OWNERS:
      return {
        ...state,
      };
    case MIS_UPDATE_TROUBLE_TICKET_OWNERS + SUCCESS:
      return {
        ...state,
        troubleTickets,
      };
    case MIS_UPDATE_TROUBLE_TICKET:
    case MIS_UPDATE_SUPPORT_TEAM:
    case MIS_CREATE_SUPPORT_TEAM:
      return {
        ...state,
        showLoading: true,
        showSuccess: false,
        showError: false,
      };
    case MIS_UPDATE_TROUBLE_TICKET + SUCCESS:
    case MIS_UPDATE_SUPPORT_TEAM + SUCCESS:
    case MIS_CREATE_SUPPORT_TEAM + SUCCESS:
      return {
        ...state,
        showLoading: false,
        showSuccess: true,
        showError: false,
      };
    case MIS_UPDATE_TROUBLE_TICKET + FAILED:
    case MIS_UPDATE_SUPPORT_TEAM + FAILED:
    case MIS_CREATE_SUPPORT_TEAM + FAILED: {
      let newState = {
        ...state,
        showError: true,
        showSuccess: false,
        showLoading: false,
      };

      if (undefined !== action.payload && undefined !== action.payload.data) {
        newState = {
          ...newState,
          errorMessage: action.payload.data["hydra:description"] ?? "",
        };
      }

      return newState;
    }
    case MIS_GET_TROUBLE_TICKETS_BY_MODULE + SUCCESS:
      return {
        ...state,
        troubleTicketsByModule: action.payload.data["hydra:member"],
        isLoading: false,
      };
    case MIS_ADD_TROUBLE_TICKETS_TO_USER_STORIES + SUCCESS:
      return {
        ...state,
        troubleTicketsRefresh: true,
      };
    case MIS_TROUBLE_TICKETS_RESET:
      return {
        ...state,
        troubleTicketsRefresh: false,
      };
    case `MIS_${HIDE_SUCCESS_ALERT}`:
      return {
        ...state,
        showSuccess: false,
        showError: false,
        showFiles: true,
      };
    case `MIS_${HIDE_ERROR_ALERT}`:
      return {
        ...state,
        showError: false,
        showSuccess: false,
      };
    default:
      break;
  }
  return state;
};

export default misReducer;
