import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/competitorPricing/competitorPricingActions";

describe("competitorsPricingActions", () => {
  describe("writeCompetitorPricing", () => {
    it("should create a write competitor pricing action", () => {
      const fakeCompetitorPricing = {
        name: "AIR",
      };
      const expectedAction = {
        type: "CPR_WRITE_COMPETITOR_PRICING",
        payload: {
          url: "/sales/competitor_pricings",
          body: fakeCompetitorPricing,
          form: "formName",
        },
      };
      expect(
        actions.writeCompetitorPricing(fakeCompetitorPricing, "formName")
      ).to.deep.equal(expectedAction);
    });
  });
});
