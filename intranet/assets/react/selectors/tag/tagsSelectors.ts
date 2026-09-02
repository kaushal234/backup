import { createSelector } from "reselect";
import Translator from "bazinga-translator";
import { RootState } from "../../store";

const getTags = (state: RootState, tags = "tags") => {
  return state.tag[tags];
};

export const getTagsMapping = createSelector([getTags], (tags) => {
  if (!tags) {
    return [];
  }
  return Object.values(tags).map((tag: any) => {
    return {
      value: tag["@id"],
      label: Translator.trans(tag.name),
    };
  });
});
