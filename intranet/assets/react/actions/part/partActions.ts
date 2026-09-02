import { PART_EDIT_PART_OBJECT } from "../../constants";

export function writePartObject(object: any, form: any) {
  return {
    type: PART_EDIT_PART_OBJECT,
    form,
    payload: {
      url: object["@id"],
      body: object,
    },
  };
}
