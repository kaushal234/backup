import { createSelector } from "reselect";
import { RootState } from "../../store";
import { IMisState } from "../../reducers/mis/misReducer";

const getModules = (state: RootState, modules: keyof IMisState = "modules") => {
  return state.mis[modules];
};

export const getModulesMapping = createSelector([getModules], (modules) => {
  if (!modules) {
    return [];
  }
  return Object.values(modules).map((module: any) => {
    return {
      value: module["@id"],
      application: module.application,
      label: `${module.name}: ${module.shortDescription} - ${module.application.name}`,
    };
  });
});
