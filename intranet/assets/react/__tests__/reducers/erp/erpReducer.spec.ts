import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/erp/erpReducer";

const initialState = {
  businessPartner: {},
  businessPartners: [],
  customer: {},
  parts: [],
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

const fakeItemPayload = {
  data: {
    "@id": "/foo/1",
    name: "foo",
    partNumber: 1065452,
  },
};

describe("erpReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });

  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });

  it("should handle ERP_FETCH_BUSINESS_PARTNER_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "ERP_FETCH_BUSINESS_PARTNER_SUCCESS",
        payload: fakeItemPayload,
      })
    ).to.deep.equal({
      ...initialState,
      businessPartner: {
        "@id": "/foo/1",
        name: "foo",
        partNumber: 1065452,
      },
    });
  });

  it("should handle ERP_FETCH_BUSINESS_PARTNER_FAILED", () => {
    expect(
      reducer(
        { ...initialState, businessPartner: { "@id": "/foo/1", name: "foo" } },
        { type: "ERP_FETCH_BUSINESS_PARTNER_FAILED" }
      )
    ).to.deep.equal({
      ...initialState,
      businessPartner: {},
    });
  });

  it("should handle ERP_FETCH_BUSINESS_PARTNERS", () => {
    expect(
      reducer(initialState, { type: "ERP_FETCH_BUSINESS_PARTNERS" })
    ).to.deep.equal({
      ...initialState,
      businessPartnersListIsLoading: true,
    });
  });

  it("should handle ERP_FETCH_BUSINESS_PARTNERS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "ERP_FETCH_BUSINESS_PARTNERS_SUCCESS",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      ...initialState,
      businessPartners: [
        { "@id": "/foo/1" },
        { "@id": "/foo/2" },
        { "@id": "/foo/3" },
      ],
      businessPartnersListIsLoading: false,
    });
  });

  it("should handle ERP_FETCH_BUSINESS_PARTNERS_FAILED", () => {
    expect(
      reducer(
        {
          ...initialState,
          businessPartners: [
            { "@id": "/foo/1" },
            { "@id": "/foo/2" },
            { "@id": "/foo/3" },
          ],
        },
        { type: "ERP_FETCH_BUSINESS_PARTNERS_FAILED" }
      )
    ).to.deep.equal({
      ...initialState,
      businessPartners: [],
      businessPartnersListIsLoading: false,
    });
  });

  it("should handle ERP_FETCH_ITEMS", () => {
    expect(reducer(initialState, { type: "ERP_FETCH_ITEMS" })).to.deep.equal({
      ...initialState,
      partsListIsLoading: true,
    });
  });

  it("should handle ERP_FETCH_ITEMS_FAILED", () => {
    expect(
      reducer(initialState, { type: "ERP_FETCH_ITEMS_FAILED" })
    ).to.deep.equal({
      ...initialState,
      parts: [],
      partsListIsLoading: false,
    });
  });

  it("should handle ERP_FETCH_ITEMS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "ERP_FETCH_ITEMS_SUCCESS",
        payload: { data: { "hydra:member": [{ foo: "bar" }] } },
      })
    ).to.deep.equal({
      ...initialState,
      parts: [{ foo: "bar" }],
      partsListIsLoading: false,
    });
  });
});
