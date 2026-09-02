import React from "react";
import { connect } from "react-redux";
import { fetchROLE as fetchRoleAction } from "../../actions/role/roleActions";
import { getRoleMapping } from "../../selectors/role/roleSelector";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  roleList: any;
  roleListIsLoading: any;
  fetchROLE: any;
  label: any;
  required: any;
  name: any;
  placeholder?: any;
  onChange?: any;
  isMulti?: any;
}

function RoleSelect(props: IProps) {
  const {
    roleList,
    roleListIsLoading,
    fetchROLE,
    onChange,
    isMulti,
    required = false,
    label = "group",
    name = "group",
    placeholder = "Type to search a ROLE",
  } = props;
  return (
    <GenericFormComponent
      type={
        isMulti ? "MutliSelectStaticDropdown" : "SingleSelectStaticDropdown"
      }
      name={name}
      label={label}
      required={required}
      list={roleList}
      placeholder={placeholder}
      onChange={onChange}
      isLoadingExternally={roleListIsLoading}
      onInputChange={(input: any) => {
        if (!input || input.length < 3) {
          return;
        }
        fetchROLE(input);
      }}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  const { role } = state;
  return {
    roleList: getRoleMapping(state),
    roleListIsLoading: role.roleListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchROLE: (search: any) => {
      dispatch(fetchRoleAction(search));
    },
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(RoleSelect);
