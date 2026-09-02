import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/manufacturing/productManufacturingsActions";

describe("productManufacturingsActions", () => {
  describe("clearData", () => {
    it("should clear form data", () => {
      const expectedAction = {
        type: "PRODUCT_MANUFACTURING_CLEAR_DATA",
        product: {
          id: 69,
          foo: "bar",
        },
        factory: "/foo/2",
        index: 2,
        year: 2019,
      };
      expect(
        actions.clearData({ id: 69, foo: "bar" }, "/foo/2", 2, 2019)
      ).to.deep.equal(expectedAction);
    });
  });
  describe("updateProductManufacturing", () => {
    it("should create an update action", () => {
      const expectedAction = {
        type: "PRODUCT_MANUFACTURING_UPDATE_PRODUCT",
        payload: {
          url: "sales/products/69?normalization_groups_override[]=product_manufacturing",
          body: { id: 69, foo: "bar" },
        },
        index: 2,
      };
      expect(
        actions.updateProductManufacturing({ id: 69, foo: "bar" }, 2)
      ).to.deep.equal(expectedAction);
    });
  });
});
