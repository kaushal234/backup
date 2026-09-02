import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getExtranetUserGroups = (state: RootState) => {
  return state.extranetUser.extranetUserGroups;
};

export const getExtranetUserGroupsMapping = createSelector(
  [getExtranetUserGroups],
  (groups) => {
    if (!groups) {
      return [];
    }
    return groups.map((group: any) => ({
      value: group["@id"],
      label: `${_.get(group, "name")}: ${_.get(group, "description")}`,
    }));
  }
);
