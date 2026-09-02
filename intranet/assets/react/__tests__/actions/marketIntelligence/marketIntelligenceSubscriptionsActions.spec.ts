import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/marketIntelligence/marketIntelligenceSubscriptionsActions";

describe("marketIntelligenceSubscriptionsActions", () => {
  describe("writeMarketIntelligenceSubscription", () => {
    it("should create a write market intelligence subscription action", () => {
      const fakeMIMSubscription = {
        mimType: "AIR",
      };
      const expectedAction = {
        type: "MIM_WRITE_MARKET_INTELLIGENCE_SUBSCRIPTION",
        payload: {
          url: "/sales/market_intelligence_subscriptions",
          body: fakeMIMSubscription,
          form: "formName",
        },
      };
      expect(
        actions.writeMarketIntelligenceSubscription(
          fakeMIMSubscription,
          "formName"
        )
      ).to.deep.equal(expectedAction);
    });
  });
});
