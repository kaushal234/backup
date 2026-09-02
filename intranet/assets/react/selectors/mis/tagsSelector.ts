import { createSelector } from "reselect";
import { RootState } from "../../store";
import { IMisState } from "../../reducers/mis/misReducer";

const getTags = (state: RootState, tags: keyof IMisState = "tags") => {
  return state.mis[tags];
};

export const getTagsMapping = createSelector([getTags], (tags) => {
  if (!tags) {
    return [];
  }
  return Object.values(tags).map((tag: any) => {
    return {
      value: tag["@id"],
      label: tag.name,
    };
  });
});
