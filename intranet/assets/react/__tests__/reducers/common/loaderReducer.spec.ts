import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/common/loaderReducer";

const initialState = {
  isLoading: false,
};

describe("loaderReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  const expectedState = {
    isLoading: true,
  };
  it("should handle COMMON_SET_LOADING", () => {
    expect(
      reducer(initialState, {
        type: "COMMON_SET_LOADING",
        payload: { isLoading: true },
      })
    ).to.deep.equal(expectedState);
  });
});
