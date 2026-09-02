import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/marketIntelligence/marketIntelligenceReducer";

const initialState = {
  marketIntelligences: [],
  marketIntelligenceTypes: [],
};

describe("marketIntelligenceReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle MIM_WRITE_MARKET_INTELLIGENCE", () => {
    expect(
      reducer(initialState, { type: "MIM_WRITE_MARKET_INTELLIGENCE" })
    ).to.deep.equal({
      ...initialState,
      showLoading: true,
      showSuccess: false,
    });
  });
  it("should handle MIM_WRITE_MARKET_INTELLIGENCE_SUCCESS", () => {
    expect(
      reducer(initialState, { type: "MIM_WRITE_MARKET_INTELLIGENCE_SUCCESS" })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: true,
    });
  });
  it("should handle MIM_HIDE_SUCCESS_ALERT", () => {
    expect(
      reducer(initialState, { type: "MIM_HIDE_SUCCESS_ALERT" })
    ).to.deep.equal({
      ...initialState,
      showSuccess: false,
      showFiles: true,
    });
  });
  it("should handle MIM_FETCH_MARKET_INTELLIGENCES", () => {
    expect(
      reducer(initialState, { type: "MIM_FETCH_MARKET_INTELLIGENCES" })
    ).to.deep.equal({
      ...initialState,
      marketIntelligencesListIsLoading: true,
    });
  });
  it("should handle MIM_FETCH_MARKET_INTELLIGENCES_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "MIM_FETCH_MARKET_INTELLIGENCES_SUCCESS",
        payload: {
          iri: "/resources/42",
          data: {
            "hydra:member": [
              { "@id": "/sales/market_intelligences/69", type: "TEST" },
              { "@id": "/sales/market_intelligences/70", type: "MIM" },
            ],
          },
        },
      })
    ).to.deep.equal({
      marketIntelligences: {
        "/sales/market_intelligences/69": {
          "@id": "/sales/market_intelligences/69",
          type: "TEST",
        },
        "/sales/market_intelligences/70": {
          "@id": "/sales/market_intelligences/70",
          type: "MIM",
        },
      },
      marketIntelligenceTypes: [],
      marketIntelligencesListIsLoading: false,
    });
  });
});
