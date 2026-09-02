import { createSelector } from "reselect";
import { RootState } from "../../store";

const buildPartNameOption = (part: any) => ({
  ...part,
  value: part.itemCode,
  label: `${part.itemCode} - ${part.description || "undefined"}`,
});
const getParts = (state: RootState) => {
  return state.part.parts;
};
export const getPartsListMapping = createSelector([getParts], (parts) => {
  if (!parts) {
    return [];
  }
  return parts.map((part: any) => buildPartNameOption(part));
});
