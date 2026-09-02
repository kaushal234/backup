import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/dms/dmsReducer";

const initialState = {
  dms: [],
};

describe("dmsReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle DMS_FETCH_DMS", () => {
    expect(
      reducer(initialState, {
        type: "DMS_FETCH_DMS",
        payload: { iri: "/resources/42" },
      })
    ).to.deep.equal({
      dms: [],
      dmsListIsLoading: true,
    });
  });
  it("should handle DMS_FETCH_DMS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "DMS_FETCH_DMS_SUCCESS",
        payload: {
          iri: "/resources/42",
          data: {
            "hydra:member": [
              { "@id": "/dms/69", title: "1458 - DMS" },
              { "@id": "/dms/70", title: "1584 - DMS test" },
            ],
          },
        },
      })
    ).to.deep.equal({
      dms: {
        "/dms/69": { "@id": "/dms/69", title: "1458 - DMS" },
        "/dms/70": { "@id": "/dms/70", title: "1584 - DMS test" },
      },
      dmsListIsLoading: false,
    });
  });
});
