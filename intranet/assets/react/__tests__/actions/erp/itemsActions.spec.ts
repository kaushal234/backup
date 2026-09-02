import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/erp/itemsAction";

describe("itemsAction", () => {
  describe("fetchItem", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "ERP_FETCH_ITEM",
        form: "form",
        payload: {
          index: null,
          request: {
            url: "/ion/items/item=1066036;site=300",
          },
        },
      };
      expect(actions.fetchItem(null, "1066036", 300, "form")).to.deep.equal(
        expectedAction
      );
    });
    it("should create a custom action", () => {
      const expectedAction = {
        type: "CUSTOM_TYPE",
        form: "CUSTOM",
        payload: {
          index: null,
          request: {
            url: "/ion/items/item=1066036;site=300",
          },
        },
      };
      expect(
        actions.fetchItem(null, "1066036", 300, "CUSTOM", "CUSTOM_TYPE")
      ).to.deep.equal(expectedAction);
    });
    it("should create an indexed action", () => {
      const expectedAction = {
        type: "ERP_FETCH_ITEM",
        form: "form",
        payload: {
          index: 7,
          request: {
            url: "/ion/items/item=1066036;site=300",
          },
        },
      };
      expect(actions.fetchItem(7, "1066036", 300, "form")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("fetchSageItems", () => {
    it("should create a fetch sage item action", () => {
      const expectedAction = {
        type: "ERP_FETCH_ITEMS",
        payload: {
          request: {
            url: "/sageparts/parts?contains[item]=1066036",
          },
        },
      };
      expect(actions.fetchSageItems("1066036")).to.deep.equal(expectedAction);
    });
  });
});
