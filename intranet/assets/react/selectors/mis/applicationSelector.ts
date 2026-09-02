import { createSelector } from "reselect";
import { RootState } from "../../store";
import { IMisState } from "../../reducers/mis/misReducer";

const getApplications = (
  state: RootState,
  applications: keyof IMisState = "applications"
) => {
  return state.mis[applications];
};

export const getApplicationsMapping = createSelector(
  [getApplications],
  (applications) => {
    if (!applications) {
      return [];
    }
    return Object.values(applications).map((application: any) => {
      return {
        value: application["@id"],
        label: application.name,
      };
    });
  }
);
