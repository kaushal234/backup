import { createSelector } from "reselect";
import { getUserDetails } from "../user/userSelectors";
import { RootState } from "../../store";
import { ILocationState } from "../../types/ILocationState";

const getLocations = (
  state: RootState,
  locationListName: keyof ILocationState = "locations"
) => {
  return state.location[locationListName];
};
const getERP = (state: RootState) => {
  return state.erp;
};

export const getLocationsMapping = createSelector(
  [getLocations],
  (locations) => {
    if (!locations) {
      return [];
    }
    return locations.map((locationData: any) => ({
      value: locationData["@id"],
      label: locationData.name,
      erp: locationData.erp,
      currency: locationData.currency ? locationData.currency.name : "",
    }));
  }
);

export const getLocationMapping = createSelector(
  [getLocationsMapping, getERP],
  (locations, erp) => {
    if (!locations || !erp) {
      return null;
    }
    return (
      locations.find((locationData: any) => erp === locationData.erp) || null
    );
  }
);

export const getUserDefaultLocationMapping = createSelector(
  [getLocationsMapping, getUserDetails],
  (locations, details) => {
    if (!locations || !details) {
      return null;
    }
    return (
      (details.erp &&
        locations.find((location: any) => details.erp === location.erp)) ||
      locations[0]
    );
  }
);
