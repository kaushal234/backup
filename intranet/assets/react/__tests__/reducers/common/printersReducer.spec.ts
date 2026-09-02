import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/common/printersReducer";

const initialState = {
  printers: [],
  loaded: false,
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

describe("printersReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  const expectedState = {
    printers: [{ "@id": "/foo/1" }, { "@id": "/foo/2" }, { "@id": "/foo/3" }],
    loaded: true,
  };
  it("should handle COMMON_FETCH_PRINTERS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "COMMON_FETCH_PRINTERS_SUCCESS",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal(expectedState);
  });
  it("should handle COMMON_FETCH_PRINTERS_FAILED", () => {
    expect(
      reducer(expectedState, {
        type: "COMMON_FETCH_PRINTERS_FAILED",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({ ...initialState, loaded: true });
  });
  it("should handle COMMON_FETCH_PRINTERS_RESET", () => {
    expect(
      reducer(expectedState, {
        type: "COMMON_FETCH_PRINTERS_RESET",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal(initialState);
  });
});
