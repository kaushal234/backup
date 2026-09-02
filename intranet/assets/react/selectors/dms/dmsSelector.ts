import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getDMS = (state: RootState) => {
  return state.dms.dms;
};

export const buildNameOption = (dms: any) => ({
  value: _.get(dms, "@id"),
  label: `${_.get(dms, "legacyId")} - ${_.get(dms, "title")}`,
});

export const getDMSMapping = createSelector([getDMS], (dmsList) => {
  if (!dmsList) {
    return [];
  }
  return Object.values(dmsList).map((dms) => buildNameOption(dms));
});
