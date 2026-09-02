import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/part/itemsMonologisticAction";

describe("itemsAction", () => {
  describe("fetchItemMonologistic", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "PART_FETCH_ITEM_MONOLOGISTIC",
        form: "form",
        payload: {
          index: null,
          request: {
            url: "/ion/item_monologistics/120?selection[]=description&selection[]=itemCode&selection[]=baseUOM",
          },
        },
      };
      expect(actions.fetchItemMonologistic(null, "120", "form")).to.deep.equal(
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
            url: "/ion/item_monologistics/120?selection[]=description&selection[]=itemCode&selection[]=baseUOM",
          },
        },
      };
      expect(
        actions.fetchItemMonologistic(null, "120", "CUSTOM", "CUSTOM_TYPE")
      ).to.deep.equal(expectedAction);
    });
    it("should create an indexed action", () => {
      const expectedAction = {
        type: "PART_FETCH_ITEM_MONOLOGISTIC",
        form: "form",
        payload: {
          index: 7,
          request: {
            url: "/ion/item_monologistics/120?selection[]=description&selection[]=itemCode&selection[]=baseUOM",
          },
        },
      };
      expect(actions.fetchItemMonologistic(7, "120", "form")).to.deep.equal(
        expectedAction
      );
    });
  });
});
