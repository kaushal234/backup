import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/customerRelationshipTeam/customerRelationshipTeamReducer";

const initialState = {
  customerRelationshipTeams: [],
  crtCreated: false,
  showSuccess: false,
  showError: false,
  showLoading: false,
  selectedCustomer: null,
  errorMessage: "",
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

describe("customerRelationshipTeamReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM", () => {
    expect(
      reducer(initialState, {
        type: "SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: true,
      crtCreated: false,
      showError: false,
    });
  });
  it("should handle SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM", () => {
    expect(
      reducer(initialState, {
        type: "SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: true,
      crtCreated: false,
      showError: false,
    });
  });
  it("should handle SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM_SUCCESS",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      crtCreated: true,
      showError: false,
    });
  });
  it("should handle SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM_SUCCESS",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showError: false,
      showSuccess: true,
    });
  });
  it("should handle SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM_FAILED", () => {
    expect(
      reducer(initialState, {
        type: "SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM_FAILED",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      crtCreated: false,
      showError: true,
    });
  });
  it("should handle SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM_FAILED", () => {
    expect(
      reducer(initialState, {
        type: "SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM_FAILED",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      crtCreated: false,
      showError: true,
    });
  });
  it("should handle CRT_HIDE_ERROR_ALERT", () => {
    expect(
      reducer(initialState, { type: "CRT_HIDE_ERROR_ALERT" })
    ).to.deep.equal({
      ...initialState,
      showSuccess: false,
    });
  });
  it("should handle CRT_HIDE_SUCCESS_ALERT", () => {
    expect(
      reducer(initialState, { type: "CRT_HIDE_SUCCESS_ALERT" })
    ).to.deep.equal({
      ...initialState,
      showError: false,
    });
  });
});
