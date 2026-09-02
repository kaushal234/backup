import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/part/partReducer";

const initialState = {
  showSuccess: false,
  showLoading: false,
  showError: false,
  parts: [],
  technicianOnCallParts: [],
};

describe("sparePartsRequestReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });

  it("should handle PART_EDIT_PART_OBJECT", () => {
    expect(
      reducer(initialState, { type: "PART_EDIT_PART_OBJECT" })
    ).to.deep.equal({
      ...initialState,
      showLoading: true,
      showSuccess: false,
      showError: false,
    });
  });
  it("should handle PART_EDIT_PART_OBJECT_FAILED", () => {
    expect(
      reducer(initialState, { type: "PART_EDIT_PART_OBJECT_FAILED" })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: false,
      showError: true,
    });
  });
  it("should handle PART_EDIT_PART_OBJECT_SUCCESS", () => {
    expect(
      reducer(initialState, { type: "PART_EDIT_PART_OBJECT_SUCCESS" })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: true,
      showError: false,
    });
  });
  it("should handle PART_HIDE_ERROR_ALERT", () => {
    expect(
      reducer(initialState, { type: "PART_HIDE_ERROR_ALERT" })
    ).to.deep.equal({
      ...initialState,
      showError: false,
    });
  });
  it("should handle PART_HIDE_SUCCESS_ALERT", () => {
    expect(
      reducer(initialState, { type: "PART_HIDE_SUCCESS_ALERT" })
    ).to.deep.equal({
      ...initialState,
      showSuccess: false,
    });
  });
});
