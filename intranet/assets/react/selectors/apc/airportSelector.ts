import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getAirports = (state: RootState) => {
  return state.apc.airports;
};

const getAirport = (state: RootState, iri: any) => {
  return state.apc.airports[iri];
};

export const buildNameOption = (airport: any) => ({
  value: _.get(airport, "@id"),
  label: `${_.get(airport, "code")} - ${_.get(airport, "cityName")}`,
});

export const getAirportsMapping = createSelector([getAirports], (airports) => {
  if (!airports) {
    return [];
  }
  return Object.values(airports).map((airport) => buildNameOption(airport));
});

export const getAirportMapping = createSelector([getAirport], (airport) => {
  if (!airport) {
    return null;
  }
  return buildNameOption(airport);
});
