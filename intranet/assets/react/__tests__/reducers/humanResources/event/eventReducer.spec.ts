import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../../reducers/humanResources/event/eventReducer";

const initialState = {
  events: [],
  showError: false,
  showLoading: false,
  showSuccess: false,
  errorMessage: "",
};

describe("eventReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle EVENT_ADD_EVENT", () => {
    expect(reducer(initialState, { type: "EVENT_ADD_EVENT" })).to.deep.equal({
      ...initialState,
      showLoading: true,
    });
  });
  it("should handle EVENT_ADD_EVENT_SUCCESS", () => {
    expect(
      reducer(initialState, { type: "EVENT_ADD_EVENT_SUCCESS" })
    ).to.deep.equal({
      ...initialState,
      showSuccess: true,
    });
  });
  it("should handle EVENT_ADD_EVENT_FAILED", () => {
    expect(
      reducer(initialState, {
        type: "EVENT_ADD_EVENT_FAILED",
        payload: { data: { "hydra:description": "foo" } },
      })
    ).to.deep.equal({
      ...initialState,
      showError: true,
      errorMessage: "foo",
    });
  });
  it("should handle EVENT_HIDE_SUCCESS_ALERT", () => {
    expect(
      reducer(initialState, { type: "EVENT_HIDE_SUCCESS_ALERT" })
    ).to.deep.equal({
      ...initialState,
    });
  });
  it("should handle EVENT_HIDE_ERROR_ALERT", () => {
    expect(
      reducer(initialState, { type: "EVENT_HIDE_ERROR_ALERT" })
    ).to.deep.equal({
      ...initialState,
    });
  });
});
