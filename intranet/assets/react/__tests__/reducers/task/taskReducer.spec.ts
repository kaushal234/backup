import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/task/taskReducer";

const initialState = {
  showSuccess: false,
  showError: false,
  showLoading: false,
  errorMessage: "",
};

describe("taskReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle CREATE_TASK", () => {
    expect(reducer(initialState, { type: "CREATE_TASK" })).to.deep.equal({
      ...initialState,
      showLoading: true,
    });
  });
  it("should handle CREATE_TASK_SUCCESS", () => {
    expect(
      reducer(initialState, { type: "CREATE_TASK_SUCCESS" })
    ).to.deep.equal({
      ...initialState,
      showSuccess: true,
    });
  });
  it("should handle CREATE_TASK_FAILED", () => {
    expect(
      reducer(initialState, {
        type: "CREATE_TASK_FAILED",
        payload: { data: { "hydra:description": "error" } },
      })
    ).to.deep.equal({
      ...initialState,
      showError: true,
      errorMessage: "error",
    });
  });
  it("should handle EDIT_TASK", () => {
    expect(reducer(initialState, { type: "EDIT_TASK" })).to.deep.equal({
      ...initialState,
      showLoading: true,
    });
  });
  it("should handle EDIT_TASK_SUCCESS", () => {
    expect(reducer(initialState, { type: "EDIT_TASK_SUCCESS" })).to.deep.equal({
      ...initialState,
      showSuccess: true,
    });
  });
  it("should handle EDIT_TASK_FAILED", () => {
    expect(
      reducer(initialState, {
        type: "EDIT_TASK_FAILED",
        payload: { data: { "hydra:description": "error" } },
      })
    ).to.deep.equal({
      ...initialState,
      showError: true,
      errorMessage: "error",
    });
  });
});
