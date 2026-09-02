import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/location/locationReducer";

const initialState = {
  locations: [],
  factories: [],
};

describe("locationReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle LOCATIONS_FETCH_FACTORIES_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "LOCATIONS_FETCH_FACTORIES_SUCCESS",
        payload: {
          data: { "hydra:member": [{ test: "factories" }] },
        },
      })
    ).to.deep.equal({
      locations: [],
      factories: [{ test: "factories" }],
    });
  });
  it("should handle LOCATIONS_FETCH_FACTORIES_FAILED", () => {
    expect(
      reducer(initialState, { type: "LOCATIONS_FETCH_FACTORIES_FAILED" })
    ).to.deep.equal({
      locations: [],
      factories: [],
    });
  });
});
