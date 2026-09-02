import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/finance/financeFamilyReducer";

const initialState = {
  financeFamilies: [],
};

const fakeItem = {
  "@id": "/foo/1",
  factories: ["/factory/1", "/factory/2"],
  pricings: {
    "/sso/1-/factory/1": {
      "@id": "/foo/1",
      averagePrice: 9,
      averageMargin: 10,
      sso: "/sso/1",
      factory: "/factory/1",
    },
    "/sso/1-/factory/2": {
      averagePrice: 9,
      averageMargin: 10,
      sso: "/sso/1",
      factory: "/factory/2",
    },
  },
};
const fakeCollectionPayload = {
  data: {
    "hydra:member": [
      { "@id": "/foo/1" },
      { "@id": "/foo/2" },
      { "@id": "/foo/3" },
    ],
  },
};

describe("financeFamilyReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle FINANCE_FETCH_FINANCE_FAMILIES_FAILED", () => {
    expect(
      reducer(initialState, {
        type: "FINANCE_FETCH_FINANCE_FAMILIES_FAILED",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      financeFamilies: [],
    });
  });
  it("should handle FINANCE_FETCH_FINANCE_FAMILIES_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "FINANCE_FETCH_FINANCE_FAMILIES_SUCCESS",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      financeFamilies: [
        { "@id": "/foo/1" },
        { "@id": "/foo/2" },
        { "@id": "/foo/3" },
      ],
    });
  });
  it("should handle FINANCE_REMOVE_PRICING", () => {
    expect(
      reducer(
        { ...initialState, financeFamilies: [fakeItem] },
        {
          type: "FINANCE_REMOVE_PRICING",
          financeFamily: fakeItem,
          payload: { index: 0 },
          sso: "/sso/1",
          factory: "/factory/1",
        }
      )
    ).to.deep.equal({
      financeFamilies: [
        {
          "@id": "/foo/1",
          factories: ["/factory/1", "/factory/2"],
          pricings: {
            "/sso/1-/factory/1": {
              "@id": "/foo/1",
              averagePrice: "",
              averageMargin: "",
              sso: "",
              factory: "",
            },
            "/sso/1-/factory/2": {
              averagePrice: 9,
              averageMargin: 10,
              sso: "/sso/1",
              factory: "/factory/2",
            },
          },
        },
      ],
    });
  });
  it("should handle FINANCE_HANDLE_FINANCE_FAMILY_FACTORIES", () => {
    expect(
      reducer(
        { ...initialState, financeFamilies: [fakeItem] },
        {
          type: "FINANCE_HANDLE_FINANCE_FAMILY_FACTORIES",
          financeFamily: fakeItem,
          payload: { index: 0 },
          sso: "/sso/1",
          factory: { value: "/factory/1" },
          value: false,
        }
      )
    ).to.deep.equal({
      financeFamilies: [
        {
          "@id": "/foo/1",
          factories: ["/factory/2"],
          pricings: {
            "/sso/1-/factory/1": {
              "@id": "/foo/1",
              averagePrice: "",
              averageMargin: "",
              sso: "",
              factory: "",
            },
            "/sso/1-/factory/2": {
              averagePrice: 9,
              averageMargin: 10,
              sso: "/sso/1",
              factory: "/factory/2",
            },
          },
        },
      ],
    });
  });
});
