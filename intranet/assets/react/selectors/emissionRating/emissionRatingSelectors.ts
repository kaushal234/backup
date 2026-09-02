import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getEmissionRatings = (state: RootState) => {
  return state.emissionRating.emissionRatings;
};

export const buildNameOption = (emissionRating: any) => ({
  value: _.get(emissionRating, "@id"),
  label: _.get(emissionRating, "name"),
});

export const getEmissionRatingsMapping = createSelector(
  [getEmissionRatings],
  (emissionRatings) => {
    if (!emissionRatings) {
      return [];
    }
    return Object.values(emissionRatings).map((emissionRating) =>
      buildNameOption(emissionRating)
    );
  }
);
