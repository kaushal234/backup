import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/competitor/competitorReducer";

const initialState = {
  competitors: [],
};

describe("competitorReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle COMPETITORS_FETCH_COMPETITORS", () => {
    expect(
      reducer(initialState, {
        type: "COMPETITORS_FETCH_COMPETITORS",
        payload: { iri: "/resources/42" },
      })
    ).to.deep.equal({
      competitors: [],
      competitorsListIsLoading: true,
    });
  });
  it("should handle COMPETITORS_FETCH_COMPETITORS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "COMPETITORS_FETCH_COMPETITORS_SUCCESS",
        payload: {
          iri: "/resources/42",
          data: {
            "hydra:member": [
              { "@id": "/sales/competitors/69", name: "AIR TEST" },
              { "@id": "/sales/competitors/70", name: "AIR" },
            ],
          },
        },
      })
    ).to.deep.equal({
      competitors: {
        "/sales/competitors/69": {
          "@id": "/sales/competitors/69",
          name: "AIR TEST",
        },
        "/sales/competitors/70": {
          "@id": "/sales/competitors/70",
          name: "AIR",
        },
      },
      competitorsListIsLoading: false,
    });
  });
});
