import { AnyAction, Reducer } from "redux";
import {
  FAILED,
  HIDE_SUCCESS_ALERT,
  SFR_CREATE_MASTER_SALES_FORECAST,
  SFR_GET_SALES_FORECAST_COMMENTS,
  SFR_GET_SALES_FORECAST_VALORIZATION,
  SFR_UPDATE_SALES_FORECAST,
  SFR_UPDATE_SALES_FORECASTS_COMPLETED,
  SUCCESS,
} from "../../constants";

interface ISalesForecastState {
  salesForecasts: Array<any>;
  sfrValorization: any;
  comments: any;
  showSuccess: boolean;
  showLoading: boolean;
}

let initialState: ISalesForecastState = {
  salesForecasts: [],
  sfrValorization: [],
  comments: [],
  showSuccess: false,
  showLoading: false,
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.sfr) {
  initialState = { ...initialState, ...window.SF_INITIAL_STORE_STATE.sfr };
}

const salesForecastReducer: Reducer<ISalesForecastState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case SFR_UPDATE_SALES_FORECAST + SUCCESS:
      return {
        ...state,
        details: action.payload.data,
      };
    case SFR_GET_SALES_FORECAST_COMMENTS + SUCCESS: {
      const comments = !state.comments ? {} : state.comments;
      return {
        ...state,
        comments: {
          ...comments,
          [action.payload.sfrIri]: action.payload.data["hydra:member"],
        },
      };
    }
    case SFR_UPDATE_SALES_FORECASTS_COMPLETED:
      return {
        ...state,
        showSuccess: true,
      };
    case `SFR_${HIDE_SUCCESS_ALERT}`:
      return {
        ...state,
        showSuccess: false,
        redirectFlag: true,
      };
    case SFR_GET_SALES_FORECAST_VALORIZATION + SUCCESS: {
      if (action.payload.data["hydra:member"].length === 0) {
        return state;
      }
      const valorization = action.payload.data["hydra:member"][0];
      const sfrValorization = !state.sfrValorization
        ? []
        : state.sfrValorization;
      return {
        ...state,
        sfrValorization: {
          ...sfrValorization,
          [`${valorization.sso}-${valorization.factory}-${valorization.financeFamily["@id"]}`]:
            valorization,
        },
      };
    }
    case SFR_CREATE_MASTER_SALES_FORECAST:
      return {
        ...state,
        showLoading: true,
        showSuccess: false,
      };
    case SFR_CREATE_MASTER_SALES_FORECAST + SUCCESS: {
      const showSuccess = action.payload.allSubmitted;
      return {
        ...state,
        showLoading: false,
        showSuccess,
      };
    }
    case SFR_CREATE_MASTER_SALES_FORECAST + FAILED:
      return {
        ...state,
        showLoading: false,
        showSuccess: false,
      };
    default:
      break;
  }
  return state;
};

export default salesForecastReducer;
