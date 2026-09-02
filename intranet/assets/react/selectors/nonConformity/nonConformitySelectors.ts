import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";
import { INonConformityState } from "../../reducers/nonConformity/nonConformityReducer";

const getNonConformitiesList = (
  state: RootState,
  nonConformities: keyof INonConformityState = "nonConformities"
) => {
  return state.nonConformity[nonConformities];
};

export const buildNameOption = (nonConformity: any) => ({
  value: _.get(nonConformity, "@id"),
  label: _.get(nonConformity, "id"),
});

export const getNonConformityListMapping = createSelector(
  [getNonConformitiesList],
  (nonConformities) => {
    if (!nonConformities) {
      return [];
    }
    return nonConformities.map((nonConformity: any) =>
      buildNameOption(nonConformity)
    );
  }
);
