import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/user/userReducer";

const initialState = {
  details: {},
  users: [],
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
const fakeItemPayload = {
  data: {
    "@id": "/foo/1",
    name: "foo",
    partNumber: 1065452,
  },
};

describe("userReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle USER_FETCH_CONNECTED_USER_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "USER_FETCH_CONNECTED_USER_SUCCESS",
        payload: fakeItemPayload,
      })
    ).to.deep.equal({
      details: {
        "@id": "/foo/1",
        name: "foo",
        partNumber: 1065452,
      },
      users: [],
    });
  });
  it("should handle USER_FETCH_USERS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "USER_FETCH_USERS_SUCCESS",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      users: [{ "@id": "/foo/1" }, { "@id": "/foo/2" }, { "@id": "/foo/3" }],
      details: {},
      usersListIsLoading: false,
    });
  });
  it("should handle USER_FETCH_USERS", () => {
    expect(reducer(initialState, { type: "USER_FETCH_USERS" })).to.deep.equal({
      users: [],
      details: {},
      usersListIsLoading: true,
    });
  });
});
