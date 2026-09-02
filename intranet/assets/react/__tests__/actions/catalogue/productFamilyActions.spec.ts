import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/catalogue/productFamilyActions";

describe("productFamilyActions", () => {
  describe("fetchProductFamilies", () => {
    it("should create a fetch products families action", () => {
      const expectedAction = {
        type: "CATALOGUE_FETCH_PRODUCT_FAMILIES",
        payload: {
          request: {
            url: "/sales/product_families?normalization_groups_override[]=catalogue_family_list&order[name]=asc&q=121",
          },
        },
      };
      expect(actions.fetchProductFamilies("121")).to.deep.equal(expectedAction);
    });
  });
});
