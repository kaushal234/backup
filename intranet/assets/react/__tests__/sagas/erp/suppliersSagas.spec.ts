import { describe, it } from "mocha";
import { expect } from "chai";

import { call, put } from "redux-saga/effects";
import { fetchAPI } from "../../../utils/api";
import {
  fetchBusinessPartners,
  fetchBusinessPartner,
} from "../../../actions/erp/businessPartnersActions";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import callGenericGetGenerator from "../../../sagas/common/generator";

describe("businessPartnersSagas", () => {
  describe("callFetchBusinessPartners", () => {
    describe("Successful calls", () => {
      const generator = callGenericGetGenerator(fetchBusinessPartners(540));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(fetchAPI, "/ion/business_partners?q=540")
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({ data: { "hydra:member": ["AF1000", "AF1001"] } })
            .value
        ).to.deep.equal(
          put(
            APICallSuccess("ERP_FETCH_BUSINESS_PARTNERS", {
              data: { "hydra:member": ["AF1000", "AF1001"] },
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callGenericGetGenerator(fetchBusinessPartners("fail"));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(fetchAPI, "/ion/business_partners?q=fail")
        );
      });
      const error = { response: "test" };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("ERP_FETCH_BUSINESS_PARTNERS", "test"))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callFetchBusinessPartner", () => {
    describe("Successful calls", () => {
      const generator = callGenericGetGenerator(fetchBusinessPartner("AF1000"));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(fetchAPI, "/ion/business_partners/AF1000")
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({ data: { suno: "AF1000", name: "AIR FRANCE" } }).value
        ).to.deep.equal(
          put(
            APICallSuccess("ERP_FETCH_BUSINESS_PARTNER", {
              data: { suno: "AF1000", name: "AIR FRANCE" },
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callGenericGetGenerator(fetchBusinessPartner("AF1000"));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(fetchAPI, "/ion/business_partners/AF1000")
        );
      });
      const error = { response: "test" };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("ERP_FETCH_BUSINESS_PARTNER", "test"))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
