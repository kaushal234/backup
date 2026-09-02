import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/catalogue/productFamilyReducer";

const initialState = {
  productFamilies: [],
};

describe("productFamilyReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle CATALOGUE_FETCH_PRODUCT_FAMILIES", () => {
    expect(
      reducer(initialState, {
        type: "CATALOGUE_FETCH_PRODUCT_FAMILIES",
        payload: { iri: "/resources/42" },
      })
    ).to.deep.equal({
      productFamilies: [],
      productFamiliesListIsLoading: true,
    });
  });
  it("should handle CATALOGUE_FETCH_PRODUCT_FAMILIES_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "CATALOGUE_FETCH_PRODUCT_FAMILIES_SUCCESS",
        payload: {
          iri: "/resources/42",
          data: {
            "hydra:member": [
              { "@id": "/sales/product_families/69", name: "121" },
              { "@id": "/sales/product_families/70", name: "NBL" },
            ],
          },
        },
      })
    ).to.deep.equal({
      productFamilies: {
        "/sales/product_families/69": {
          "@id": "/sales/product_families/69",
          name: "121",
        },
        "/sales/product_families/70": {
          "@id": "/sales/product_families/70",
          name: "NBL",
        },
      },
      productFamiliesListIsLoading: false,
    });
  });
});
