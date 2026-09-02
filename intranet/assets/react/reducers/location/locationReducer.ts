import { AnyAction, Reducer } from "redux";
import {
  SUCCESS,
  FAILED,
  LOCATIONS_FETCH_LOCATIONS,
  LOCATION_FETCH_FACTORIES,
} from "../../constants";
import { ILocationState } from "../../types/ILocationState";

let initialState: ILocationState = {
  locations: [],
  factories: [],
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.location) {
  initialState = { ...initialState, ...window.SF_INITIAL_STORE_STATE.location };
}

const locationReducer: Reducer<ILocationState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case LOCATIONS_FETCH_LOCATIONS + SUCCESS:
      return {
        ...state,
        locations: action.payload.data["hydra:member"],
      };
    case LOCATIONS_FETCH_LOCATIONS + FAILED:
      return {
        ...state,
        locations: [],
      };
    case LOCATION_FETCH_FACTORIES + SUCCESS:
      return {
        ...state,
        factories: action.payload.data["hydra:member"],
      };
    case LOCATION_FETCH_FACTORIES + FAILED:
      return {
        ...state,
      };
    default:
      break;
  }
  return state;
};

export default locationReducer;
