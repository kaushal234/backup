import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { getApplicationsMapping } from "../../../selectors/mis/applicationSelector";
import { RootState } from "../../../store";
import GenericFormComponent from "../../GenericFormComponent/GenericFormComponent";
import { IDropdownItem } from "../../../types/IDropdownItem";

interface IProps {
  applications: Array<IDropdownItem>;
  name: string;
  required?: boolean;
  onChange?: (event: IDropdownItem, value: IDropdownItem) => void;
}

function ApplicationSelect(props: IProps) {
  const { applications, name = "application", required, onChange } = props;

  const updatedApplications = [
    {
      value: "",
      label: Translator.trans(
        "trouble_ticket.form.option_value.all_applications"
      ),
    },
    ...applications,
  ];
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      list={updatedApplications}
      placeholder="Search by name"
      name={name}
      required={required}
      onChange={onChange}
      label={Translator.trans("trouble_ticket.fields.application")}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    applications: getApplicationsMapping(state),
  };
};

export default connect(mapStateToProps)(ApplicationSelect);
