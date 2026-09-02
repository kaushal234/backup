import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { fetchUsersAsyncList } from "../../actions/user/userActions";
import { getUsersListMapping } from "../../selectors/user/userSelectors";
import { AppDispatch } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  users: any;
  usersListIsLoading: any;
  fetchUsersList: any;
  label: any;
  required: any;
  name: any;
  placeholder: any;
  onChange: any;
  filter: any;
  customFilter: any;
  excluded?: any;
  isMulti?: boolean;
  isDisabled?: any;
}

function PeopleAsyncSelect({
  users,
  usersListIsLoading,
  fetchUsersList,
  onChange,
  filter,
  customFilter,
  label = Translator.trans("home.quick_search.people_label"),
  required = false,
  name = "people",
  placeholder = Translator.trans("home.quick_search.placeholder"),
  isMulti,
  ...props
}: IProps) {
  return (
    <GenericFormComponent
      type={
        isMulti ? "MutliSelectStaticDropdown" : "SingleSelectStaticDropdown"
      }
      name={name}
      label={label}
      required={required}
      list={users}
      placeholder={placeholder}
      onChange={onChange}
      isLoadingExternally={usersListIsLoading}
      onInputChange={(input: any) => {
        if (!input || input.length < 3) {
          return;
        }
        fetchUsersList(input, filter, customFilter);
      }}
      {...props}
    />
  );
}

const mapStateToProps = (state: any) => {
  const { user } = state;
  return {
    users: getUsersListMapping(state),
    usersListIsLoading: user.usersListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchUsersList: (search: any, filter: any, customFilter: any) => {
      dispatch(fetchUsersAsyncList(search, filter, customFilter));
    },
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(PeopleAsyncSelect);
