import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill } from "redux-form";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import { client } from "../../../store";
import { writeCompetitorPricing } from "../../../actions/competitorPricing/competitorPricingActions";
import { callWriteCompetitorPricing } from "../../../sagas/competitorPricing/competitorPricingSagas";

describe("competitorPricingSagas", () => {
  describe("callWriteCompetitorPricing", () => {
    describe("Successful calls", () => {
      const generator = callWriteCompetitorPricing(
        writeCompetitorPricing({ foo: "bar" }, "form_name")
      );

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/sales/competitor_pricings", { foo: "bar" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { foo: "bar" } }).value).to.deep.equal(
          put(
            APICallSuccess("CPR_WRITE_COMPETITOR_PRICING", {
              data: { foo: "bar" },
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callWriteCompetitorPricing(
        writeCompetitorPricing({ foo: "bar" }, "form_name")
      );
      const error = {
        response: { data: { "hydra:description": "ECHEC" }, index: 12 },
      };
      it("should first fetch the API for the first SFR", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/sales/competitor_pricings", { foo: "bar" })
        );
      });
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed("CPR_WRITE_COMPETITOR_PRICING", {
              data: { "hydra:description": "ECHEC" },
              index: 12,
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form_name", "competitorPricing.errorMessage", "ECHEC"))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
