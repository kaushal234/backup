import React from "react";
import { connect } from "react-redux";
import { getExtranetUserGroupsMapping } from "../../selectors/extranetUser/extranetUserGroups";
import { RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  groupsList: any;
  required: any;
  name: any;
  label: any;
  placeholder: any;
}

function ExtranetUserGroupsSelect({
  groupsList,
  required = true,
  name = "extranetUserGroups",
  label = "Roles",
  placeholder = "Search by name",
  ...props
}: IProps) {
  return (
    <GenericFormComponent
      type="MutliSelectStaticDropdown"
      list={groupsList}
      name={name}
      placeholder={placeholder}
      label={label}
      required={required}
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    groupsList: getExtranetUserGroupsMapping(state),
  };
};

export default connect(mapStateToProps)(ExtranetUserGroupsSelect);
