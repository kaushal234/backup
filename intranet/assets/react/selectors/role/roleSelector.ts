import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getRole = (state: RootState) => {
  return state.role.role;
};

export const buildNameOption = (role: any) => ({
  value: _.get(role, "@id"),
  label: _.get(role, "name"),
});

export const getRoleMapping = createSelector([getRole], (roleList) => {
  if (!roleList) {
    return [];
  }
  return Object.values(roleList).map((role) => buildNameOption(role));
});
