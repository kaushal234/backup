import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/extranetUser/extranetUserReducer";

const initialState = {
  extranetUsers: [],
  extranetUserGroups: [],
  submittedExtranetUserGroup: 0,
  extranetUsersListIsLoading: false,
  showLoading: false,
  showError: false,
  showSuccess: false,
  extranetUserListEmpty: false,
};

const fakeCollectionPayload = {
  data: {
    "hydra:member": [
      { "@id": "/foo/1" },
      { "@id": "/foo/2" },
      { "@id": "/foo/3" },
    ],
  },
};

describe("extranetuserReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle EXTRANET_USER_FETCH_EXTRANET_USERS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "EXTRANET_USER_FETCH_EXTRANET_USERS_SUCCESS",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      extranetUsers: [
        { "@id": "/foo/1" },
        { "@id": "/foo/2" },
        { "@id": "/foo/3" },
      ],
      extranetUserGroups: [],
      submittedExtranetUserGroup: 0,
      extranetUsersListIsLoading: false,
      extranetUserListEmpty: false,
      showLoading: false,
      showError: false,
      showSuccess: false,
    });
  });
  it("should handle EXTRANET_USER_FETCH_EXTRANET_USERS_FAILED", () => {
    expect(
      reducer(initialState, {
        type: "EXTRANET_USER_FETCH_EXTRANET_USERS_FAILED",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      extranetUsers: [],
      extranetUserGroups: [],
      submittedExtranetUserGroup: 0,
      extranetUsersListIsLoading: false,
      showLoading: false,
      showError: false,
      showSuccess: false,
      extranetUserListEmpty: false,
    });
  });
  it("should handle EXTRANET_USER_FETCH_EXTRANET_USERS", () => {
    expect(
      reducer(initialState, { type: "EXTRANET_USER_FETCH_EXTRANET_USERS" })
    ).to.deep.equal({
      extranetUsers: [],
      extranetUserGroups: [],
      submittedExtranetUserGroup: 0,
      extranetUsersListIsLoading: true,
      showLoading: false,
      showError: false,
      showSuccess: false,
      extranetUserListEmpty: false,
    });
  });
  it("should handle EXTRANET_USER_CREATE_EXTRANET_USER_ACLS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "EXTRANET_USER_CREATE_EXTRANET_USER_ACLS_SUCCESS",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      extranetUsers: [],
      extranetUserGroups: [],
      submittedExtranetUserGroup: 1,
      extranetUsersListIsLoading: false,
      showLoading: false,
      showError: false,
      showSuccess: true,
      extranetUserListEmpty: false,
    });
  });
  it("should handle EXTRANET_USER_CREATE_EXTRANET_USER_ACLS_FAILED", () => {
    expect(
      reducer(initialState, {
        type: "EXTRANET_USER_CREATE_EXTRANET_USER_ACLS_FAILED",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      extranetUsers: [],
      extranetUserGroups: [],
      submittedExtranetUserGroup: 0,
      extranetUsersListIsLoading: false,
      showLoading: false,
      showError: true,
      showSuccess: false,
      extranetUserListEmpty: false,
    });
  });
  it("should handle EXTRANET_USER_CREATE_EXTRANET_USER_ACLS", () => {
    expect(
      reducer(initialState, { type: "EXTRANET_USER_CREATE_EXTRANET_USER_ACLS" })
    ).to.deep.equal({
      extranetUsers: [],
      extranetUserGroups: [],
      submittedExtranetUserGroup: 0,
      extranetUsersListIsLoading: false,
      showLoading: true,
      showError: false,
      showSuccess: false,
      extranetUserListEmpty: false,
    });
  });
  it("should handle EXTRANET_USER_HIDE_ERROR_ALERT", () => {
    expect(
      reducer(initialState, { type: "EXTRANET_USER_HIDE_ERROR_ALERT" })
    ).to.deep.equal({
      extranetUsers: [],
      extranetUserGroups: [],
      submittedExtranetUserGroup: 0,
      extranetUsersListIsLoading: false,
      showLoading: false,
      showError: false,
      showSuccess: false,
      extranetUserListEmpty: false,
    });
  });
  it("should handle EXTRANET_USER_HIDE_SUCCESS_ALERT", () => {
    expect(
      reducer(initialState, { type: "EXTRANET_USER_HIDE_SUCCESS_ALERT" })
    ).to.deep.equal({
      extranetUsers: [],
      extranetUserGroups: [],
      submittedExtranetUserGroup: 0,
      extranetUsersListIsLoading: false,
      showLoading: false,
      showError: false,
      showSuccess: false,
      extranetUserListEmpty: false,
    });
  });
});
