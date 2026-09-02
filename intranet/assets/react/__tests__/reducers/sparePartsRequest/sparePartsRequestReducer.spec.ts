import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/sparePartsRequest/sparePartsRequestReducer";

const initialState = {
  parts: [],
  deliveryAddresses: { discr: [] },
  showError: false,
  showLoading: false,
  showSuccess: false,
  sparePartsRequests: [],
  submittedSPR: 0,
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

  it("should handle SPR_FETCH_DELIVERY_ADDRESSES_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "SPR_FETCH_DELIVERY_ADDRESSES_SUCCESS",
        payload: {
          iri: "/resources/42",
          discriminator: "discr",
          data: { "hydra:member": [{ foo: "bar" }] },
        },
      })
    ).to.deep.equal({
      ...initialState,
      deliveryAddresses: { discr: [{ foo: "bar" }] },
    });
  });
  it("should handle SPR_CREATE_TOC_SPARE_PARTS_REQUEST", () => {
    expect(
      reducer(initialState, { type: "SPR_CREATE_TOC_SPARE_PARTS_REQUEST" })
    ).to.deep.equal({
      ...initialState,
      showLoading: true,
      showSuccess: false,
      showError: false,
    });
  });
  it("should handle SPR_EDIT_TOC_SPARE_PARTS_REQUEST", () => {
    expect(
      reducer(initialState, { type: "SPR_EDIT_TOC_SPARE_PARTS_REQUEST" })
    ).to.deep.equal({
      ...initialState,
      showLoading: true,
      showSuccess: false,
      showError: false,
    });
  });
  it("should handle SPR_CREATE_SB_SPARE_PARTS_REQUEST", () => {
    expect(
      reducer(initialState, { type: "SPR_CREATE_SB_SPARE_PARTS_REQUEST" })
    ).to.deep.equal({
      ...initialState,
      showLoading: true,
      showSuccess: false,
      showError: false,
    });
  });
  it("should handle SPR_CREATE_TOC_SPARE_PARTS_REQUEST_FAILED", () => {
    expect(
      reducer(initialState, {
        type: "SPR_CREATE_TOC_SPARE_PARTS_REQUEST_FAILED",
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: false,
      showError: true,
    });
  });
  it("should handle SPR_CREATE_SB_SPARE_PARTS_REQUEST_FAILED", () => {
    expect(
      reducer(initialState, {
        type: "SPR_CREATE_SB_SPARE_PARTS_REQUEST_FAILED",
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: false,
      showError: true,
    });
  });
  it("should handle SPR_EDIT_TOC_SPARE_PARTS_REQUEST_FAILED", () => {
    expect(
      reducer(initialState, { type: "SPR_EDIT_TOC_SPARE_PARTS_REQUEST_FAILED" })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: false,
      showError: true,
    });
  });
  it("should handle SPR_CREATE_TOC_SPARE_PARTS_REQUEST_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "SPR_CREATE_TOC_SPARE_PARTS_REQUEST_SUCCESS",
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: true,
      showError: false,
    });
  });
  it("should handle SPR_CREATE_SB_SPARE_PARTS_REQUEST_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "SPR_CREATE_SB_SPARE_PARTS_REQUEST_SUCCESS",
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: true,
      showError: false,
      submittedSPR: 1,
    });
  });
  it("should handle SPR_EDIT_TOC_SPARE_PARTS_REQUEST_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "SPR_EDIT_TOC_SPARE_PARTS_REQUEST_SUCCESS",
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: true,
      showError: false,
    });
  });
});
