import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/erp/businessPartnersActions";

describe("businessPartnersActions", () => {
  describe("fetchBusinessPartners", () => {
    it("should create a fetch action with erp", () => {
      const expectedAction = {
        type: "ERP_FETCH_BUSINESS_PARTNERS",
        payload: {
          request: {
            url: "/ion/business_partners?q=SUNO",
          },
        },
      };
      expect(actions.fetchBusinessPartners("SUNO")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("fetchBusinessPartner", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "ERP_FETCH_BUSINESS_PARTNER",
        payload: {
          request: {
            url: "/ion/business_partners/AF1000",
          },
        },
      };
      expect(actions.fetchBusinessPartner("AF1000")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("fetchSageSuppliers", () => {
    it("should create a fetch action with sage erp", () => {
      const expectedAction = {
        type: "ERP_FETCH_SUPPLIERS",
        payload: {
          request: {
            url: "/sageparts/suppliers?contains[name]=SUNO",
          },
        },
      };
      expect(actions.fetchSageSuppliers("SUNO")).to.deep.equal(expectedAction);
    });
  });
});
