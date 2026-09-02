import { AnyAction, Reducer } from "redux";
import {
  SUCCESS,
  MIM_WRITE_MARKET_INTELLIGENCE,
  HIDE_SUCCESS_ALERT,
  MIM_FETCH_MARKET_INTELLIGENCES,
  FAILED,
} from "../../constants";

interface IMarketIntelligenceState {
  marketIntelligences: Array<any>;
  marketIntelligenceTypes: Array<any>;
  marketIntelligencesListIsLoading?: boolean;
  showLoading?: any;
  showSuccess?: any;
  showFiles?: any;
  details?: any;
}

let initialState: IMarketIntelligenceState = {
  marketIntelligences: [],
  marketIntelligenceTypes: [],
};

if (
  window.SF_INITIAL_STORE_STATE &&
  window.SF_INITIAL_STORE_STATE.marketIntelligence
) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.marketIntelligence,
  };
}

const marketIntelligenceReducer: Reducer<
  IMarketIntelligenceState,
  AnyAction
  // eslint-disable-next-line default-param-last
> = (state = initialState, action) => {
  const marketIntelligences: any = {};
  switch (action.type) {
    case MIM_WRITE_MARKET_INTELLIGENCE:
      return {
        ...state,
        showLoading: true,
        showSuccess: false,
      };
    case MIM_WRITE_MARKET_INTELLIGENCE + FAILED:
      return {
        ...state,
        showLoading: false,
        showSuccess: false,
      };
    case MIM_WRITE_MARKET_INTELLIGENCE + SUCCESS:
      return {
        ...state,
        showLoading: false,
        showSuccess: true,
      };
    case `MIM_${HIDE_SUCCESS_ALERT}`:
      return {
        ...state,
        showSuccess: false,
        showFiles: true,
      };
    case MIM_FETCH_MARKET_INTELLIGENCES:
      return {
        ...state,
        marketIntelligencesListIsLoading: true,
      };
    case MIM_FETCH_MARKET_INTELLIGENCES + SUCCESS:
      action.payload.data["hydra:member"].forEach((marketIntelligence: any) => {
        marketIntelligences[marketIntelligence["@id"]] = marketIntelligence;
      });
      return {
        ...state,
        marketIntelligences,
        marketIntelligencesListIsLoading: false,
      };
    default:
      break;
  }
  return state;
};

export default marketIntelligenceReducer;
