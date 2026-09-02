import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { getModulesMapping } from "../../../selectors/mis/moduleSelector";
import { RootState } from "../../../store";
import { IDropdownItem } from "../../../types/IDropdownItem";
import GenericFormComponent from "../../GenericFormComponent/GenericFormComponent";

interface IProps {
  modules: any;
  name: string;
  required?: boolean;
  onChange?: (value: IDropdownItem) => void;
  isDisabled?: boolean;
}

function ModulesSelect(props: IProps) {
  const { modules, name = "module", required, onChange, isDisabled } = props;
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      list={modules}
      name={name}
      placeholder="Search by name"
      required={required}
      onChange={onChange}
      disabled={isDisabled}
      label={Translator.trans("mis.changelog.fields.module")}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    modules: getModulesMapping(state),
  };
};

export default connect(mapStateToProps)(ModulesSelect);
