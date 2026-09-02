import { AnyAction, Reducer } from "redux";
import {
  NON_CONFORMITY_FETCH_NON_CONFORMITIES,
  SUCCESS,
} from "../../constants";

export interface INonConformityState {
  nonConformities: any;
  nonConformitiesListIsLoading?: boolean;
}

let initialState: INonConformityState = {
  nonConformities: [],
};
if (
  window.SF_INITIAL_STORE_STATE &&
  window.SF_INITIAL_STORE_STATE.nonConformity
) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.nonConformity,
  };
}

const nonConformityReducer: Reducer<INonConformityState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case NON_CONFORMITY_FETCH_NON_CONFORMITIES:
      state = {
        ...state,
        nonConformitiesListIsLoading: true,
      };
      break;
    case NON_CONFORMITY_FETCH_NON_CONFORMITIES + SUCCESS:
      state = {
        ...state,
        nonConformities: action.payload.data["hydra:member"],
        nonConformitiesListIsLoading: false,
      };
      break;
    default:
      break;
  }
  return state;
};

export default nonConformityReducer;
