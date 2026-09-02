import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/apc/airportReducer";

const initialState = {
  airports: [],
};

describe("airportReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle APC_FETCH_AIRPORTS", () => {
    expect(
      reducer(initialState, {
        type: "APC_FETCH_AIRPORTS",
        payload: { iri: "/resources/42" },
      })
    ).to.deep.equal({
      airports: [],
      airportsListIsLoading: true,
    });
  });
  it("should handle APC_FETCH_AIRPORTS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "APC_FETCH_AIRPORTS_SUCCESS",
        payload: {
          iri: "/resources/42",
          data: {
            "hydra:member": [
              { "@id": "/airports/69", name: "manteau" },
              { "@id": "/airports/70", name: "clef" },
            ],
          },
        },
      })
    ).to.deep.equal({
      airports: {
        "/airports/69": { "@id": "/airports/69", name: "manteau" },
        "/airports/70": { "@id": "/airports/70", name: "clef" },
      },
      airportsListIsLoading: false,
    });
  });
  it("should handle APC_FETCH_AIRPORT_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "APC_FETCH_AIRPORT_SUCCESS",
        payload: {
          iri: "/airports/999",
          data: { "@id": "/airports/999", name: "feuille" },
        },
      })
    ).to.deep.equal({
      airports: {
        "/airports/999": { "@id": "/airports/999", name: "feuille" },
      },
    });
  });
});
