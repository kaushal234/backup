import React from "react";
import { connect } from "react-redux";
import { getBusinessUnitsMapping } from "../../selectors/businessUnit/businessUnitSelector";
import { RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  businessUnits: any;
  required: any;
  name: any;
  label: any;
  placeholder: any;
  isMulti?: boolean;
  isDisabled?: boolean;
}

function BusinessUnitsSelect({
  required = true,
  name = "businessUnit",
  label = "Business unit",
  placeholder = "Search by name",
  businessUnits,
  isMulti,
  isDisabled,
  ...props
}: IProps) {
  return (
    <GenericFormComponent
      type={
        isMulti ? "MutliSelectStaticDropdown" : "SingleSelectStaticDropdown"
      }
      list={businessUnits}
      name={name}
      placeholder={placeholder}
      label={label}
      required={required}
      disabled={isDisabled}
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    businessUnits: getBusinessUnitsMapping(state),
  };
};

export default connect(mapStateToProps)(BusinessUnitsSelect);
