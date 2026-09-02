import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";
import { IUserState } from "../../reducers/user/userReducer";

const getUsersList = (state: RootState, users: keyof IUserState = "users") => {
  return state.user[users];
};

const getIri = (state: any) => {
  return state.iri;
};

export const buildNameOption = (person: any) => ({
  value: _.get(person, "@id"),
  label: `${_.get(person, "lastname")} ${_.get(person, "firstname")} - ${_.get(
    person,
    "email"
  )}`,
});

export const getUsersListMapping = createSelector([getUsersList], (users) => {
  if (!users) {
    return [];
  }
  return users.map((user: any) => buildNameOption(user));
});

export const getUserMapping = createSelector(
  [getUsersListMapping, getIri],
  (users, iri) => {
    if (!users) {
      return null;
    }
    return users.find((userData: any) => iri === userData.value) || null;
  }
);

export const getUserDetails = (state: RootState) => {
  return state.user.details;
};
