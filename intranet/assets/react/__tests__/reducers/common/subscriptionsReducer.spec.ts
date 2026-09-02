import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/common/subscriptionsReducer";

const initialState = {
  subscriptions: {},
  pendingCreations: {},
  pendingDeletions: {},
};

describe("subscriptionsReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle COMMON_FETCH_SUBSCRIPTIONS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "COMMON_FETCH_SUBSCRIPTIONS_SUCCESS",
        payload: {
          iri: "/resources/42",
          data: { "hydra:member": [{ test: "faux lower" }] },
        },
      })
    ).to.deep.equal({
      subscriptions: {
        "/resources/42": [{ test: "faux lower" }],
      },
      pendingCreations: {},
      pendingDeletions: {},
    });
  });
  it("should handle COMMON_CREATE_SUBSCRIPTION", () => {
    expect(
      reducer(initialState, {
        type: "COMMON_CREATE_SUBSCRIPTION",
        payload: { request: { body: { resource: "/resources/404" } } },
      })
    ).to.deep.equal({
      subscriptions: {},
      pendingCreations: { "/resources/404": true },
      pendingDeletions: {},
    });
  });
  it("should handle COMMON_CREATE_SUBSCRIPTION_SUCCESS", () => {
    const existingState = {
      subscriptions: {},
      pendingCreations: { "/resources/43": true },
      pendingDeletions: {},
    };
    expect(
      reducer(existingState, {
        type: "COMMON_CREATE_SUBSCRIPTION_SUCCESS",
        payload: {
          data: { "@id": "/subscriptions/12", resource: "/resources/43" },
        },
      })
    ).to.deep.equal({
      subscriptions: {
        "/resources/43": [
          { "@id": "/subscriptions/12", resource: "/resources/43" },
        ],
      },
      pendingCreations: {},
      pendingDeletions: {},
    });
  });
  it("should handle COMMON_CREATE_SUBSCRIPTION_SUCCESS with existing subscriptions on same resource", () => {
    const existingState = {
      subscriptions: {
        "/resources/43": [
          { "@id": "/subscriptions/1", resource: "/resources/43" },
        ],
      },
      pendingCreations: { "/resources/43": true },
      pendingDeletions: {},
    };
    expect(
      reducer(existingState, {
        type: "COMMON_CREATE_SUBSCRIPTION_SUCCESS",
        payload: {
          data: { "@id": "/subscriptions/2", resource: "/resources/43" },
        },
      })
    ).to.deep.equal({
      subscriptions: {
        "/resources/43": [
          { "@id": "/subscriptions/1", resource: "/resources/43" },
          { "@id": "/subscriptions/2", resource: "/resources/43" },
        ],
      },
      pendingCreations: {},
      pendingDeletions: {},
    });
  });
  it("should handle COMMON_DELETE_SUBSCRIPTION", () => {
    const existingState = {
      subscriptions: {
        "/resources/44": [
          { "@id": "/subscriptions/1", resource: "/resources/43" },
          { "@id": "/subscriptions/3", resource: "/resources/43" },
        ],
      },
      pendingCreations: {},
      pendingDeletions: {},
    };
    expect(
      reducer(existingState, {
        type: "COMMON_DELETE_SUBSCRIPTION",
        payload: {
          subscriptionIri: "/subscriptions/1",
          resourceIri: "/resources/44",
        },
      })
    ).to.deep.equal({
      subscriptions: {
        "/resources/44": [
          { "@id": "/subscriptions/1", resource: "/resources/43" },
          { "@id": "/subscriptions/3", resource: "/resources/43" },
        ],
      },
      pendingCreations: {},
      pendingDeletions: { "/subscriptions/1": true },
    });
  });
  it("should handle COMMON_DELETE_SUBSCRIPTION_SUCCESS", () => {
    const existingState = {
      subscriptions: {
        "/resources/44": [
          { "@id": "/subscriptions/1", resource: "/resources/43" },
          { "@id": "/subscriptions/3", resource: "/resources/43" },
        ],
      },
      pendingCreations: {},
      pendingDeletions: { "/subscriptions/1": true },
    };
    expect(
      reducer(existingState, {
        type: "COMMON_DELETE_SUBSCRIPTION_SUCCESS",
        payload: {
          subscriptionIri: "/subscriptions/1",
          resourceIri: "/resources/44",
        },
      })
    ).to.deep.equal({
      subscriptions: {
        "/resources/44": [
          { "@id": "/subscriptions/3", resource: "/resources/43" },
        ],
      },
      pendingCreations: {},
      pendingDeletions: {},
    });
  });
});
