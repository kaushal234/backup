import { AnyAction, Reducer } from "redux";
import {
  USER_FETCH_CONNECTED_USER,
  USER_FETCH_USERS,
  SUCCESS,
} from "../../constants";

export interface IUserState {
  details: any;
  users: Array<any>;
  usersListIsLoading?: boolean;
  qam?: any;
  asms?: any;
  canCreateCsr?: boolean;
  canCreateExtranetUser?: boolean;
}

let initialState: IUserState = {
  details: {},
  users: [],
};
if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.user) {
  initialState = { ...initialState, ...window.SF_INITIAL_STORE_STATE.user };
}

const userReducer: Reducer<IUserState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case USER_FETCH_CONNECTED_USER + SUCCESS:
      state = {
        ...state,
        details: action.payload.data,
      };
      break;
    case USER_FETCH_USERS:
      state = {
        ...state,
        usersListIsLoading: true,
      };
      break;
    case USER_FETCH_USERS + SUCCESS:
      state = {
        ...state,
        users: action.payload.data["hydra:member"],
        usersListIsLoading: false,
      };
      break;
    default:
      break;
  }
  return state;
};

export default userReducer;
