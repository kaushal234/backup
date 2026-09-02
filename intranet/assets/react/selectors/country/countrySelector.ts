import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getCountries = (state: RootState) => {
  return state.country.countries;
};

export const buildNameOption = (country: any) => ({
  value: _.get(country, "@id"),
  label: _.get(country, "name"),
  isoCode2: _.get(country, "isoCode2"),
});

export const getCountriesMapping = createSelector(
  [getCountries],
  (countries) => {
    if (!countries) {
      return [];
    }
    return Object.values(countries).map((country) => buildNameOption(country));
  }
);
