import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../../reducers/quality/supplierCorrectiveRequest/SupplierCorrectiveActiveRequestReducer";
import {
  CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
  FAILED,
  SUCCESS,
  UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
} from "../../../../constants";

const initialState = {
  showSuccess: false,
  showError: false,
  showLoading: false,
  showFiles: false,
  id: null,
  errorMessage: "",
};

describe("SupplierCorrectiveActiveRequestReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST", () => {
    expect(
      reducer(initialState, { type: CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST })
    ).to.deep.equal({
      ...initialState,
      showLoading: true,
    });
  });
  it("should handle CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST + SUCCESS,
        payload: {
          data: {
            id: 1,
          },
        },
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: true,
      id: 1,
    });
  });
  it("should handle CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST_FAILED", () => {
    const payloadData = { "hydra:description": "cut error" };
    expect(
      reducer(initialState, {
        type: CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST + FAILED,
        payload: { data: payloadData },
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showError: true,
      errorMessage: payloadData["hydra:description"],
    });
  });
  it("should handle UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST", () => {
    expect(
      reducer(initialState, { type: UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST })
    ).to.deep.equal({
      ...initialState,
      showLoading: true,
    });
  });
  it("should handle UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST + SUCCESS,
        payload: {
          data: {
            id: 1,
          },
        },
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: true,
      id: 1,
    });
  });
  it("should handle UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST_FAILED", () => {
    const payloadData = { "hydra:description": "cut error" };
    expect(
      reducer(initialState, {
        type: UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST + FAILED,
        payload: { data: payloadData },
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showError: true,
      errorMessage: payloadData["hydra:description"],
    });
  });
});
