import { describe, it } from "mocha";
import { expect } from "chai";
import { getMarketIntelligencesMapping } from "../../../selectors/marketIntelligence/marketIntelligenceSelect";

const initialState: any = {
  marketIntelligence: {
    marketIntelligences: [],
  },
};

describe("marketIntelligenceSelect", () => {
  describe("getMarketIntelligencesMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        marketIntelligence: {
          marketIntelligences: {
            "/foo/1": { "@id": "/foo/1", id: 1, shortDescription: "foo" },
            "/bar/2": { "@id": "/bar/2", id: 2, shortDescription: "bar" },
          },
        },
      };
      expect(getMarketIntelligencesMapping(initialState)).to.deep.equal([]);
      expect(getMarketIntelligencesMapping.recomputations()).to.equal(1);
      expect(
        getMarketIntelligencesMapping(state).map((marketIntelligence) => {
          return { ...marketIntelligence };
        })
      ).to.deep.equal([
        { label: "1 - foo", value: "/foo/1" },
        { label: "2 - bar", value: "/bar/2" },
      ]);
      expect(getMarketIntelligencesMapping.recomputations()).to.equal(2);
      getMarketIntelligencesMapping(state);
      expect(getMarketIntelligencesMapping.recomputations()).to.equal(2);
    });
  });
});
