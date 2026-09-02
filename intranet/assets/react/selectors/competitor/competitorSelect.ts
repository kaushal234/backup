import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getCompetitors = (state: RootState) => {
  return state.competitor.competitors;
};

export const buildNameOption = (competitor: any) => ({
  value: _.get(competitor, "@id"),
  label: _.get(competitor, "name"),
});

export const getCompetitorsMapping = createSelector(
  [getCompetitors],
  (competitors) => {
    if (!competitors) {
      return [];
    }
    return Object.values(competitors).map((competitor) =>
      buildNameOption(competitor)
    );
  }
);
