import { describe, it } from "mocha";
import { expect } from "chai";
import {
  getProductsManufacturingMapping,
  getProductsMapping,
} from "../../../selectors/catalogue/productSelector";

const initialState: any = {
  product: {
    products: [],
  },
};

describe("productSelector", () => {
  describe("getProductsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        product: {
          products: {
            "/foo/1": {
              "@id": "/foo/1",
              name: "Foo",
              financeFamily: { "@id": "/ff/1", name: "la ff" },
            },
            "/foo/2": { "@id": "/foo/2", name: "Bar", financeFamily: null },
          },
        },
      };
      expect(getProductsMapping(initialState)).to.deep.equal([]);
      expect(getProductsMapping.recomputations()).to.equal(1);
      expect(
        getProductsMapping(state).map((product) => {
          return { ...product };
        })
      ).to.deep.equal([
        {
          "@id": "/foo/1",
          name: "Foo",
          index: 0,
          label: "Foo",
          value: "/foo/1",
          financeFamily: {
            value: "/ff/1",
            label: "la ff",
          },
        },
        {
          "@id": "/foo/2",
          name: "Bar",
          label: "Bar",
          value: "/foo/2",
          index: 1,
          financeFamily: null,
        },
      ]);
      expect(getProductsMapping.recomputations()).to.equal(2);
      getProductsMapping(state);
      expect(getProductsMapping.recomputations()).to.equal(2);
    });
  });
  describe("productSelector", () => {
    describe("getProductsManufacturingMapping", () => {
      it("should use memoization", () => {
        const state: any = {
          product: {
            products: [
              {
                "@id": "/foo/1",
                name: "Foo",
                label: "Foo",
                value: "/foo/1",
                productManufacturings: [{ foo: "bar" }],
              },
            ],
          },
        };
        expect(getProductsManufacturingMapping(initialState)).to.deep.equal([]);
        expect(getProductsManufacturingMapping.recomputations()).to.equal(1);
        expect(getProductsManufacturingMapping(state)).to.deep.equal([
          {
            "@id": "/foo/1",
            name: "Foo",
            label: "Foo",
            value: "/foo/1",
            productManufacturings: [{ foo: "bar" }],
          },
        ]);
        expect(getProductsManufacturingMapping.recomputations()).to.equal(2);
        getProductsManufacturingMapping(state);
        expect(getProductsManufacturingMapping.recomputations()).to.equal(2);
      });
    });
  });
});
