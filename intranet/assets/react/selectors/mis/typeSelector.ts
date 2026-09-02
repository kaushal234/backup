import { createSelector } from "reselect";
import Translator from "bazinga-translator";
import { RootState } from "../../store";
import { IMisState } from "../../reducers/mis/misReducer";

const getTypes = (state: RootState, types: keyof IMisState = "types") => {
  return state.mis[types];
};

export const getTypesMapping = createSelector([getTypes], (types) => {
  if (!types) {
    return [];
  }
  return Object.values(types).map((type: any) => {
    return {
      value: type["@id"],
      label: Translator.trans(
        `trouble_ticket.form.option_value.${type.description}`
      ),
      id: type.id,
    };
  });
});
