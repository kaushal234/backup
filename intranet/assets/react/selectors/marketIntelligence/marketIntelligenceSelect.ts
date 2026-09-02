import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getMarketIntelligences = (state: RootState) => {
  return state.marketIntelligence.marketIntelligences;
};

export const buildNameOption = (marketIntelligence: any) => ({
  value: _.get(marketIntelligence, "@id"),
  label: `${_.get(marketIntelligence, "id")} - ${_.get(
    marketIntelligence,
    "shortDescription"
  )}`,
});

export const getMarketIntelligencesMapping = createSelector(
  [getMarketIntelligences],
  (marketIntelligences) => {
    if (!marketIntelligences) {
      return [];
    }
    return Object.values(marketIntelligences).map((marketIntelligence) =>
      buildNameOption(marketIntelligence)
    );
  }
);
