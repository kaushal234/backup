import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/catalogue/productReducer";

const initialState = {
  products: [],
};

const fakeItemPayload = {
  url: "/foo/1",
  data: {
    "@id": "/foo/1",
    financeFamily: "/bar/1",
  },
};

const fakeItem = {
  "@id": "/sales/products/999",
  productManufacturings: {
    "/foo-/sales/products/999-2019": {
      industrialIncorporationParameter: 145,
      factoryStandardEfficiency: 100,
      modelBaseHours: 150,
    },
  },
};

describe("productReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle CATALOGUE_UPDATE_PRODUCT", () => {
    expect(
      reducer(initialState, {
        type: "CATALOGUE_UPDATE_PRODUCT",
        payload: fakeItemPayload,
      })
    ).to.deep.equal({
      products: {
        "/foo/1": {
          showLoader: true,
          showSuccess: false,
        },
      },
    });
  });
  it("should handle CATALOGUE_UPDATE_PRODUCT_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "CATALOGUE_UPDATE_PRODUCT_SUCCESS",
        payload: fakeItemPayload,
      })
    ).to.deep.equal({
      products: {
        "/foo/1": {
          showLoader: false,
          showSuccess: true,
          financeFamily: "/bar/1",
        },
      },
    });
  });
  it("should handle CATALOGUE_FETCH_PRODUCTS", () => {
    expect(
      reducer(initialState, {
        type: "CATALOGUE_FETCH_PRODUCTS",
        payload: { iri: "/resources/42" },
      })
    ).to.deep.equal({
      products: [],
      productsListIsLoading: true,
    });
  });
  it("should handle CATALOGUE_FETCH_PRODUCTS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "CATALOGUE_FETCH_PRODUCTS_SUCCESS",
        payload: {
          iri: "/resources/42",
          data: {
            "hydra:member": [
              { "@id": "/sales/products/69", name: "121" },
              { "@id": "/sales/products/70", name: "NBL" },
            ],
          },
        },
      })
    ).to.deep.equal({
      products: {
        "/sales/products/69": { "@id": "/sales/products/69", name: "121" },
        "/sales/products/70": { "@id": "/sales/products/70", name: "NBL" },
      },
      productsListIsLoading: false,
    });
  });
  it("should handle CATALOGUE_FETCH_PRODUCT_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "CATALOGUE_FETCH_PRODUCT_SUCCESS",
        payload: {
          iri: "/sales/products/999",
          data: { "@id": "/sales/products/999", name: "RBL" },
        },
      })
    ).to.deep.equal({
      products: {
        "/sales/products/999": { "@id": "/sales/products/999", name: "RBL" },
      },
    });
  });
  it("should handle PRODUCT_MANUFACTURING_CLEAR_DATA", () => {
    expect(
      reducer(
        { ...initialState, products: [fakeItem] },
        {
          type: "PRODUCT_MANUFACTURING_CLEAR_DATA",
          factory: "/foo",
          index: 0,
          year: "2019",
          product: fakeItem,
        }
      )
    ).to.deep.equal({
      products: [
        {
          "@id": "/sales/products/999",
          productManufacturings: {
            "/foo-/sales/products/999-2019": {
              factoryStandardEfficiency: null,
              industrialIncorporationParameter: null,
              modelBaseHours: null,
            },
          },
        },
      ],
    });
  });
});
