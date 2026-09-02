import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/marketIntelligence/marketIntelligenceSubscriptionReducer";

const initialState = {
  marketIntelligenceSubscriptions: [],
};

describe("marketIntelligenceSubscriptionReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle MIM_WRITE_MARKET_INTELLIGENCE_SUBSCRIPTION", () => {
    expect(
      reducer(initialState, {
        type: "MIM_WRITE_MARKET_INTELLIGENCE_SUBSCRIPTION",
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: true,
      showSuccess: false,
    });
  });
  it("should handle MIM_WRITE_MARKET_INTELLIGENCE_SUBSCRIPTION_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "MIM_WRITE_MARKET_INTELLIGENCE_SUBSCRIPTION_SUCCESS",
      })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: true,
    });
  });
  it("should handle MIM_NOT_HIDE_SUCCESS_ALERT", () => {
    expect(
      reducer(initialState, { type: "MIM_NOT_HIDE_SUCCESS_ALERT" })
    ).to.deep.equal({
      ...initialState,
      showSuccess: false,
      redirectFlag: true,
    });
  });
});
