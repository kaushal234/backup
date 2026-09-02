import { AnyAction, Reducer } from "redux";
import { ROLE_FETCH_ROLE, SUCCESS } from "../../constants";

interface IRoleState {
  role: Array<any>;
  roleListIsLoading: boolean;
}

const initialState: IRoleState = {
  role: [],
  roleListIsLoading: false,
};

const roleReducer: Reducer<IRoleState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case ROLE_FETCH_ROLE + SUCCESS: {
      const role: any = {};
      action.payload.data["hydra:member"].forEach((singleROLE: any) => {
        role[singleROLE["@id"]] = singleROLE;
      });
      return {
        ...state,
        role,
        roleListIsLoading: false,
      };
    }
    case ROLE_FETCH_ROLE:
      return {
        ...state,
        roleListIsLoading: true,
      };
    default:
      break;
  }
  return state;
};

export default roleReducer;
