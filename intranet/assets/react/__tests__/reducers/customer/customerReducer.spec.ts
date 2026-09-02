import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/customer/customerReducer";

const initialState = {
  customers: [],
};

describe("customerReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle SALES_FETCH_CUSTOMERS", () => {
    expect(
      reducer(initialState, { type: "SALES_FETCH_CUSTOMERS" })
    ).to.deep.equal({
      customers: [],
      customersListIsLoading: true,
      customerNotFound: false,
      showSuccess: false,
      showError: false,
    });
  });
  it("should handle SALES_FETCH_CUSTOMERS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "SALES_FETCH_CUSTOMERS_SUCCESS",
        payload: {
          name: "AIR",
          iri: "/resources/42",
          data: {
            "hydra:member": [
              { "@id": "/sales/customers/69", name: "AIR TEST" },
              { "@id": "/sales/customers/70", name: "AIR" },
            ],
          },
        },
      })
    ).to.deep.equal({
      customers: {
        "/sales/customers/69": {
          "@id": "/sales/customers/69",
          name: "AIR TEST",
        },
        "/sales/customers/70": { "@id": "/sales/customers/70", name: "AIR" },
      },
      customersListIsLoading: false,
      customerNotFound: false,
      showSuccess: false,
      showError: false,
    });
  });
  it("should handle SALES_FETCH_CUSTOMERS_SUCCESS with no result", () => {
    expect(
      reducer(initialState, {
        type: "SALES_FETCH_CUSTOMERS_SUCCESS",
        payload: {
          name: "AIR",
          iri: "/resources/42",
          data: { "hydra:member": [] },
        },
      })
    ).to.deep.equal({
      customers: [],
      customersListIsLoading: false,
      customerNotFound: true,
      showSuccess: false,
      showError: false,
      inputName: "AIR",
    });
  });
  it("should handle SALES_FETCH_CUSTOMER_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "SALES_FETCH_CUSTOMER_SUCCESS",
        payload: {
          name: "AIR",
          iri: "/resources/42",
          data: { "@id": "/sales/customers/69", name: "AIR TEST" },
        },
      })
    ).to.deep.equal({
      customers: {
        "/sales/customers/69": {
          "@id": "/sales/customers/69",
          name: "AIR TEST",
        },
      },
    });
  });
  it("should handle SALES_CREATE_CUSTOMER_FORM_SUCCESS", () => {
    expect(
      reducer(initialState, { type: "SALES_CREATE_CUSTOMER_FORM_SUCCESS" })
    ).to.deep.equal({
      customers: [],
      customerNotFound: false,
      showSuccess: true,
      showError: false,
    });
  });
  it("should handle SALES_CREATE_CUSTOMER_FORM_FAILED", () => {
    expect(
      reducer(initialState, { type: "SALES_CREATE_CUSTOMER_FORM_FAILED" })
    ).to.deep.equal({
      customers: [],
      customerNotFound: false,
      showSuccess: false,
      showError: true,
    });
  });
  it("should handle SALES_CLEAR_CUSTOMERS", () => {
    expect(
      reducer(initialState, { type: "SALES_CLEAR_CUSTOMERS" })
    ).to.deep.equal(initialState);
  });
});
