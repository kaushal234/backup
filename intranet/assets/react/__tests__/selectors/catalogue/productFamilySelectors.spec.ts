import { describe, it } from "mocha";
import { expect } from "chai";
import { getProductFamiliesMapping } from "../../../selectors/catalogue/productFamilySelector";

const initialState: any = {
  productFamily: {
    productFamilies: [],
  },
};

describe("productFamilySelector", () => {
  describe("getProductFamiliesMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        productFamily: {
          productFamilies: {
            "/foo/1": { "@id": "/foo/1", name: "Foo" },
            "/foo/2": { "@id": "/foo/2", name: "Bar" },
          },
        },
      };
      expect(getProductFamiliesMapping(initialState)).to.deep.equal([]);
      expect(getProductFamiliesMapping.recomputations()).to.equal(1);
      expect(
        getProductFamiliesMapping(state).map((productFamily) => {
          return { ...productFamily };
        })
      ).to.deep.equal([
        {
          label: "Foo",
          value: "/foo/1",
        },
        {
          label: "Bar",
          value: "/foo/2",
        },
      ]);
      expect(getProductFamiliesMapping.recomputations()).to.equal(2);
      getProductFamiliesMapping(state);
      expect(getProductFamiliesMapping.recomputations()).to.equal(2);
    });
  });
});
