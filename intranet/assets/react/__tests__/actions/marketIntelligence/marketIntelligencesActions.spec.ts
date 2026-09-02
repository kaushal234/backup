import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/marketIntelligence/marketIntelligencesActions";

describe("marketIntelligencesActions", () => {
  describe("fetchMarketIntelligences", () => {
    it("should create a fetch market intelligence action", () => {
      const expectedAction = {
        type: "MIM_FETCH_MARKET_INTELLIGENCES",
        payload: {
          request: {
            url: "/sales/market_intelligences?normalizationGroupsOverride[]=market_intelligence:list&order[id]=asc&id=1",
          },
        },
      };
      expect(actions.fetchMarketIntelligences("1")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("writeMarketIntelligence", () => {
    it("should create a write market intelligence action", () => {
      const fakeMIM = {
        mimType: "AIR",
      };
      const expectedAction = {
        type: "MIM_WRITE_MARKET_INTELLIGENCE",
        payload: {
          url: "/sales/market_intelligences",
          body: fakeMIM,
          form: "formName",
        },
      };
      expect(
        actions.writeMarketIntelligence(fakeMIM, "formName")
      ).to.deep.equal(expectedAction);
    });
  });
});
