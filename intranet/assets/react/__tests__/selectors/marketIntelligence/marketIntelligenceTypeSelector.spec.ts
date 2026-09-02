import { describe, it } from "mocha";
import { expect } from "chai";
import { getMarketIntelligenceTypesMapping } from "../../../selectors/marketIntelligence/marketIntelligenceTypeSelect";

const initialState: any = {
  marketIntelligence: {
    marketIntelligenceTypes: [],
  },
};

describe("marketIntelligenceTypeSelect", () => {
  describe("getMarketIntelligenceTypesMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        marketIntelligence: {
          marketIntelligenceTypes: {
            "/foo/1": { "@id": "/foo/1", name: "toto" },
            "/bar/2": { "@id": "/bar/2", name: "tata" },
          },
        },
      };
      expect(getMarketIntelligenceTypesMapping(initialState)).to.deep.equal([]);
      expect(getMarketIntelligenceTypesMapping.recomputations()).to.equal(1);
      expect(
        getMarketIntelligenceTypesMapping(state).map(
          (marketIntelligenceType) => {
            return { ...marketIntelligenceType };
          }
        )
      ).to.deep.equal([
        { label: "toto", value: "/foo/1" },
        { label: "tata", value: "/bar/2" },
      ]);
      expect(getMarketIntelligenceTypesMapping.recomputations()).to.equal(2);
      getMarketIntelligenceTypesMapping(state);
      expect(getMarketIntelligenceTypesMapping.recomputations()).to.equal(2);
    });
  });
});
