import { IDropdownItem } from "../../@type/IDropdownItem";
import { INTERVENTION_STATUS_MAP } from "../../constants/constants";

export const createInterventionStatusDropdownItem = (
  item: string
): IDropdownItem => {
  return {
    id: item,
    text: INTERVENTION_STATUS_MAP[item] ?? item,
  };
};
