import React from "react";
import { connect } from "react-redux";
import { fetchExtranetUsersList as fetchExtranetUsersListAction } from "../../actions/extranetUser/extranetUserActions";
import { getExtranetUsersMapping } from "../../selectors/extranetUser/extranetUserSelectors";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  getExtranetUsers: any;
  extranetUsersListIsLoading: any;
  fetchExtranetUsersList: any;
  label: any;
  required: any;
  name: any;
  placeholder: any;
  onChange: any;
  async: any;
  discriminator: any;
  extranetUsers?: any;
  isMulti?: boolean;
}

function ExtranetUserAsyncSelect({
  getExtranetUsers,
  extranetUsersListIsLoading,
  fetchExtranetUsersList,
  onChange,
  discriminator,
  async = true,
  required = false,
  label = "eContact",
  name = "contact",
  placeholder = "Search a Contact",
  isMulti,
  ...props
}: IProps) {
  const extranetUsers = getExtranetUsers(discriminator);
  return (
    <GenericFormComponent
      type={
        isMulti ? "MutliSelectStaticDropdown" : "SingleSelectStaticDropdown"
      }
      name={name}
      label={label}
      required={required}
      list={extranetUsers}
      placeholder={placeholder}
      onChange={onChange}
      isLoadingExternally={extranetUsersListIsLoading}
      onInputChange={(input: any) => {
        if (!async || !input || input.length < 3) {
          return;
        }
        fetchExtranetUsersList(input);
      }}
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  const { extranetUser } = state;
  return {
    getExtranetUsers: (discriminator: any) =>
      getExtranetUsersMapping(state, discriminator),
    extranetUsersListIsLoading: extranetUser.usersListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchExtranetUsersList: (search: any) => {
      dispatch(fetchExtranetUsersListAction(search));
    },
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(ExtranetUserAsyncSelect);
