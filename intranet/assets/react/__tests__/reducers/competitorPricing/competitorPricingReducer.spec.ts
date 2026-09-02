import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/competitorPricing/competitorPricingReducer";

const initialState = {
  competitorPricings: [],
};

const fakeItemPayload = {
  data: {
    "@id": "/foo/1",
    bar: "foo",
  },
};

describe("competitorPricingReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle CPR_WRITE_COMPETITOR_PRICING", () => {
    expect(
      reducer(initialState, {
        type: "CPR_WRITE_COMPETITOR_PRICING",
        payload: fakeItemPayload,
      })
    ).to.deep.equal(initialState);
  });
  it("should handle CPR_WRITE_COMPETITOR_PRICING_SUCCESS", () => {
    expect(
      reducer(initialState, { type: "CPR_WRITE_COMPETITOR_PRICING_SUCCESS" })
    ).to.deep.equal({
      ...initialState,
      showError: false,
    });
  });
  it("should handle CPR_WRITE_COMPETITOR_PRICING_FAILED", () => {
    expect(
      reducer(initialState, { type: "CPR_WRITE_COMPETITOR_PRICING_FAILED" })
    ).to.deep.equal({
      ...initialState,
      showError: true,
    });
  });
  it("should handle CPR_HIDE_ERROR_ALERT", () => {
    expect(
      reducer(initialState, { type: "CPR_HIDE_ERROR_ALERT" })
    ).to.deep.equal({
      ...initialState,
      showError: false,
    });
  });
});
