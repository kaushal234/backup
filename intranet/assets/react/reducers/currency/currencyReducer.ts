import { AnyAction, Reducer } from "redux";
import { CURRENCY_FETCH_CURRENCIES, SUCCESS } from "../../constants";

interface ICurrencyState {
  currencies: Array<any>;
}

let initialState: ICurrencyState = {
  currencies: [],
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.currency) {
  initialState = { ...initialState, ...window.SF_INITIAL_STORE_STATE.currency };
}

const currencyReducer: Reducer<ICurrencyState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case CURRENCY_FETCH_CURRENCIES + SUCCESS:
      return {
        ...state,
        currencies: action.payload.data["hydra:member"],
      };
    default:
      break;
  }
  return state;
};

export default currencyReducer;
