import { AnyAction, Reducer } from "redux";
import {
  SUCCESS,
  EXTRANET_USER_FETCH_EXTRANET_USERS,
  EXTRANET_USER_CREATE_EXTRANET_USER_ACLS,
  FAILED,
  HIDE_ERROR_ALERT,
  HIDE_SUCCESS_ALERT,
} from "../../constants";

interface IExtranetUserState {
  extranetUsers: Array<any>;
  extranetUserGroups: Array<any>;
  submittedExtranetUserGroup: number;
  extranetUsersListIsLoading: boolean;
  showLoading: boolean;
  showError: boolean;
  showSuccess: boolean;
  extranetUserListEmpty: boolean;
  usersListIsLoading?: boolean;
}

let initialState: IExtranetUserState = {
  extranetUsers: [],
  extranetUserGroups: [],
  submittedExtranetUserGroup: 0,
  extranetUsersListIsLoading: false,
  showLoading: false,
  showError: false,
  showSuccess: false,
  extranetUserListEmpty: false,
};
if (
  window.SF_INITIAL_STORE_STATE &&
  window.SF_INITIAL_STORE_STATE.extranetUser
) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.extranetUser,
  };
}

const extranetUserReducer: Reducer<IExtranetUserState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case EXTRANET_USER_FETCH_EXTRANET_USERS:
      return {
        ...state,
        extranetUsersListIsLoading: true,
      };
    case EXTRANET_USER_FETCH_EXTRANET_USERS + SUCCESS:
      return {
        ...state,
        extranetUsers: action.payload.data["hydra:member"],
        extranetUsersListIsLoading: false,
        extranetUserListEmpty: action.payload.data["hydra:member"].length === 0,
      };
    case EXTRANET_USER_FETCH_EXTRANET_USERS + FAILED:
      return {
        ...state,
        extranetUsersListIsLoading: false,
      };
    case EXTRANET_USER_CREATE_EXTRANET_USER_ACLS:
      return {
        ...state,
        showLoading: true,
        showError: false,
        showSuccess: false,
      };
    case EXTRANET_USER_CREATE_EXTRANET_USER_ACLS + SUCCESS:
      return {
        ...state,
        submittedExtranetUserGroup: state.submittedExtranetUserGroup + 1,
        showLoading: false,
        showError: false,
        showSuccess: true,
      };
    case EXTRANET_USER_CREATE_EXTRANET_USER_ACLS + FAILED:
      return {
        ...state,
        showLoading: false,
        showError: true,
        showSuccess: false,
      };
    case `EXTRANET_USER_${HIDE_ERROR_ALERT}`:
      return {
        ...state,
        showError: false,
      };
    case `EXTRANET_USER_${HIDE_SUCCESS_ALERT}`:
      return {
        ...state,
        showSuccess: false,
      };
    default:
      break;
  }
  return state;
};

export default extranetUserReducer;
