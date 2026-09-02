import { AnyAction, Reducer } from "redux";
import {
  API_FETCH_COUNTRY,
  API_FETCH_COUNTRIES,
  SUCCESS,
} from "../../constants";

interface ICountryState {
  countries: Array<any>;
  countriesListIsLoading?: boolean;
}

const initialState: ICountryState = {
  countries: [],
};

const countryReducer: Reducer<ICountryState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case API_FETCH_COUNTRIES:
      return {
        ...state,
        countriesListIsLoading: true,
      };
    case API_FETCH_COUNTRIES + SUCCESS:
      const countries: any = {};
      action.payload.data["hydra:member"].forEach((country: any) => {
        countries[country["@id"]] = country;
      });
      return {
        ...state,
        countries,
        countriesListIsLoading: false,
      };
    case API_FETCH_COUNTRY + SUCCESS:
      return {
        ...state,
        countries: {
          ...state.countries,
          [action.payload.data["@id"]]: action.payload.data,
        },
      };
    default:
      break;
  }
  return state;
};

export default countryReducer;
