import { AnyAction, Reducer } from "redux";
import {
  SUCCESS,
  HIDE_SUCCESS_ALERT,
  MIM_WRITE_MARKET_INTELLIGENCE_SUBSCRIPTION,
} from "../../constants";

interface IMarketIntelligenceSubscriptionState {
  marketIntelligenceSubscriptions: Array<any>;
  showLoading?: boolean;
  showSuccess?: boolean;
}

let initialState: IMarketIntelligenceSubscriptionState = {
  marketIntelligenceSubscriptions: [],
};

if (
  window.SF_INITIAL_STORE_STATE &&
  window.SF_INITIAL_STORE_STATE.marketIntelligenceSubscription
) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.marketIntelligenceSubscription,
  };
}

const marketIntelligenceSubscriptionReducer: Reducer<
  IMarketIntelligenceSubscriptionState,
  AnyAction
> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case MIM_WRITE_MARKET_INTELLIGENCE_SUBSCRIPTION:
      return {
        ...state,
        showLoading: true,
        showSuccess: false,
      };
    case MIM_WRITE_MARKET_INTELLIGENCE_SUBSCRIPTION + SUCCESS:
      return {
        ...state,
        showLoading: false,
        showSuccess: true,
      };
    case `MIM_NOT_${HIDE_SUCCESS_ALERT}`:
      return {
        ...state,
        showSuccess: false,
        redirectFlag: true,
      };
    default:
      break;
  }
  return state;
};

export default marketIntelligenceSubscriptionReducer;
