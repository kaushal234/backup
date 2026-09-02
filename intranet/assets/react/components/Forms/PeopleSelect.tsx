import React from "react";
import { connect } from "react-redux";
import { getUsersListMapping } from "../../selectors/user/userSelectors";
import { RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  users?: any;
  inline?: any;
  excluded?: any;
  name: any;
  label?: any;
  userListName?: any;
  required?: any;
  isDisabled?: any;
  isClearable?: any;
  isMulti?: boolean;
  placeholder?: any;
}

interface IMappedProps {
  userListName?: any;
}

function PeopleSelect({
  users,
  excluded = [],
  placeholder = "Search by name",
  isMulti,
  ...props
}: IProps) {
  users = users.filter((user: any) => !excluded.includes(user.value));

  return (
    <div>
      <GenericFormComponent
        type={
          isMulti ? "MutliSelectStaticDropdown" : "SingleSelectStaticDropdown"
        }
        list={users}
        placeholder={placeholder}
        {...props}
      />
    </div>
  );
}

const mapStateToProps = (
  state: RootState,
  { userListName }: IProps & IMappedProps
) => {
  return {
    users: getUsersListMapping(state, userListName || "users"),
  };
};

export default connect(mapStateToProps)(PeopleSelect);
