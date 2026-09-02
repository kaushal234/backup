import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getMarketIntelligenceTypes = (state: RootState) => {
  return state.marketIntelligence.marketIntelligenceTypes;
};

export const buildNameOption = (marketIntelligenceType: any) => ({
  value: _.get(marketIntelligenceType, "@id"),
  label: _.get(marketIntelligenceType, "name"),
});

export const getMarketIntelligenceTypesMapping = createSelector(
  [getMarketIntelligenceTypes],
  (marketIntelligenceTypes) => {
    if (!marketIntelligenceTypes) {
      return [];
    }
    return Object.values(marketIntelligenceTypes).map(
      (marketIntelligenceType) => buildNameOption(marketIntelligenceType)
    );
  }
);
