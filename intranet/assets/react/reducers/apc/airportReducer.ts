import { AnyAction, Reducer } from "redux";
import {
  APC_FETCH_AIRPORT,
  APC_FETCH_AIRPORTS,
  SUCCESS,
} from "../../constants";

interface IAirportState {
  airports: Array<any>;
  airportsListIsLoading?: boolean;
}

const initialState: IAirportState = {
  airports: [],
};

const airportReducer: Reducer<IAirportState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case APC_FETCH_AIRPORTS:
      return {
        ...state,
        airportsListIsLoading: true,
      };
    case APC_FETCH_AIRPORTS + SUCCESS:
      const airports: any = {};
      action.payload.data["hydra:member"].forEach((airport: any) => {
        airports[airport["@id"]] = airport;
      });
      return {
        ...state,
        airports,
        airportsListIsLoading: false,
      };
    case APC_FETCH_AIRPORT + SUCCESS:
      return {
        ...state,
        airports: {
          ...state.airports,
          [action.payload.data["@id"]]: action.payload.data,
        },
      };
    default:
      break;
  }
  return state;
};

export default airportReducer;
