import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/businessUnit/businessUnitReducer";

const initialState = {
  businessUnits: [],
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

describe("businessUnitReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle DIRECTORY_FETCH_BUSINESS_UNITS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "DIRECTORY_FETCH_BUSINESS_UNITS_SUCCESS",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      businessUnits: {
        "/foo/1": { "@id": "/foo/1" },
        "/foo/2": { "@id": "/foo/2" },
        "/foo/3": { "@id": "/foo/3" },
      },
    });
  });
  it("should handle DIRECTORY_FETCH_BUSINESS_UNITS", () => {
    expect(
      reducer(initialState, { type: "DIRECTORY_FETCH_BUSINESS_UNITS" })
    ).to.deep.equal({
      businessUnits: [],
    });
  });
});
