import { AnyAction, Reducer } from "redux";
import { INCOTERM_FETCH_TERMS_OF_DELIVERIES, SUCCESS } from "../../constants";

interface IIncotermState {
  termsOfDelivery: Array<any>;
}

let initialState: IIncotermState = {
  termsOfDelivery: [],
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.incoterms) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.incoterms,
  };
}

const incotermReducer: Reducer<IIncotermState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case INCOTERM_FETCH_TERMS_OF_DELIVERIES + SUCCESS:
      return {
        ...state,
        termsOfDelivery: action.payload.data["hydra:member"],
      };
    default:
      break;
  }
  return state;
};

export default incotermReducer;
