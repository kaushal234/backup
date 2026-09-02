import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/finance/financeFamiliesActions";

describe("financeFamiliesActions", () => {
  describe("updateFinanceFamily", () => {
    it("should create an update action", () => {
      const expectedAction = {
        type: "FINANCE_UPDATE_FINANCE_FAMILY",
        payload: {
          url: "/finance/finance_families/69",
          body: { id: 69, foo: "bar" },
        },
        index: 2,
      };
      expect(
        actions.writeFinanceFamily({ id: 69, foo: "bar" }, 2)
      ).to.deep.equal(expectedAction);
    });
  });
  describe("createFinanceFamily", () => {
    it("should create a create action", () => {
      const expectedAction = {
        type: "FINANCE_CREATE_FINANCE_FAMILY",
        payload: {
          url: "/finance/finance_families",
          body: { foo: "bar" },
        },
        index: 2,
      };
      expect(actions.writeFinanceFamily({ foo: "bar" }, 2)).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("handleFinanceFamilyFactories", () => {
    it("should handles factories of finance family", () => {
      const expectedAction = {
        type: "FINANCE_HANDLE_FINANCE_FAMILY_FACTORIES",
        financeFamily: { foo: "bar" },
        factory: "/factory/1",
        value: "trou",
        index: 6,
        sso: "/sso/1",
      };
      expect(
        actions.handleFinanceFamilyFactories(
          { foo: "bar" },
          "/factory/1",
          "trou",
          6,
          "/sso/1"
        )
      ).to.deep.equal(expectedAction);
    });
  });
  describe("removePricing", () => {
    it("should remove pricing from finance family", () => {
      const expectedAction = {
        type: "FINANCE_REMOVE_PRICING",
        financeFamily: { foo: "bar" },
        sso: "/sso/1",
        factory: "/factory/1",
        index: 6,
      };
      expect(
        actions.removePricing({ foo: "bar" }, "/sso/1", "/factory/1", 6)
      ).to.deep.equal(expectedAction);
    });
  });
  describe("deleteFinanceFamily", () => {
    it("should delete finance family", () => {
      const expectedAction = {
        type: "FINANCE_DELETE_FINANCE_FAMILY",
        payload: {
          index: 15,
          url: "/finance/finance_families/6",
        },
      };
      expect(actions.deleteFinanceFamily(6, 15)).to.deep.equal(expectedAction);
    });
  });
});
