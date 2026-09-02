import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/catalogue/productsActions";

describe("productsActions", () => {
  describe("fetchProduct", () => {
    it("should create a fetch product action", () => {
      const expectedAction = {
        type: "CATALOGUE_FETCH_PRODUCT",
        payload: {
          request: {
            url: "/resource/1",
          },
        },
      };
      expect(actions.fetchProduct("/resource/1")).to.deep.equal(expectedAction);
    });
  });
  describe("fetchProducts", () => {
    it("should create a fetch products action", () => {
      const expectedAction = {
        type: "CATALOGUE_FETCH_PRODUCTS",
        payload: {
          request: {
            url: "/sales/products?normalization_groups_override[]=product_list_pricing&order[name]=asc&hidden=0&q=121",
          },
        },
      };
      expect(actions.fetchProducts("121")).to.deep.equal(expectedAction);
    });
  });
  describe("updateProduct", () => {
    it("should create an update action", () => {
      const expectedAction = {
        type: "CATALOGUE_UPDATE_PRODUCT",
        payload: {
          url: "/sales/products/68",
          body: { id: 68, foo: "bar" },
        },
      };
      expect(actions.updateProduct({ id: 68, foo: "bar" })).to.deep.equal(
        expectedAction
      );
    });
  });
});
