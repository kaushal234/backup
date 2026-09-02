import { AnyAction, Reducer } from "redux";
import {
  CPR_WRITE_COMPETITOR_PRICING,
  FAILED,
  HIDE_ERROR_ALERT,
  SUCCESS,
} from "../../constants";

interface ICompetitorPricingState {
  competitorPricings: Array<any>;
  showError?: any;
}

let initialState: ICompetitorPricingState = {
  competitorPricings: [],
};

if (
  window.SF_INITIAL_STORE_STATE &&
  window.SF_INITIAL_STORE_STATE.competitorPricing
) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.competitorPricing,
  };
}

const competitorPricingReducer: Reducer<ICompetitorPricingState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case CPR_WRITE_COMPETITOR_PRICING:
      return {
        ...state,
      };
    case CPR_WRITE_COMPETITOR_PRICING + SUCCESS:
      return {
        ...state,
        showError: false,
      };
    case `CPR_${HIDE_ERROR_ALERT}`:
      return {
        ...state,
        showError: false,
      };
    case CPR_WRITE_COMPETITOR_PRICING + FAILED:
      return {
        ...state,
        showError: true,
      };
    default:
      break;
  }
  return state;
};

export default competitorPricingReducer;
