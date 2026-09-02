import { AnyAction, Reducer } from "redux";
import { COMMON_FETCH_PRINTERS, SUCCESS, FAILED, RESET } from "../../constants";

interface IPrintersState {
  printers: Array<any>;
  loaded: boolean;
}

const initialState: IPrintersState = {
  printers: [],
  loaded: false,
};

const printersReducer: Reducer<IPrintersState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case COMMON_FETCH_PRINTERS + SUCCESS:
      return {
        ...state,
        printers: action.payload.data["hydra:member"],
        loaded: true,
      };
    case COMMON_FETCH_PRINTERS + RESET:
    case COMMON_FETCH_PRINTERS + FAILED:
      return {
        ...state,
        printers: [],
        loaded: action.type === COMMON_FETCH_PRINTERS + FAILED,
      };
    default:
      break;
  }
  return state;
};

export default printersReducer;
