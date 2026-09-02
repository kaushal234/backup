import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { getTypesMapping } from "../../../selectors/mis/typeSelector";
import { RootState } from "../../../store";
import GenericFormComponent from "../../GenericFormComponent/GenericFormComponent";
import { IDropdownItem } from "../../../types/IDropdownItem";

interface IProps {
  types: any;
  name: string;
  required?: boolean;
  onChange?: (event: IDropdownItem, value: IDropdownItem) => void;
}

function TypesSelect(props: IProps) {
  const { types, name = "descriptionType", required, onChange } = props;
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      list={types}
      placeholder="Select one"
      name={name}
      required={required}
      onChange={onChange}
      label={Translator.trans("trouble_ticket.fields.option")}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    types: getTypesMapping(state),
  };
};

export default connect(mapStateToProps)(TypesSelect);
