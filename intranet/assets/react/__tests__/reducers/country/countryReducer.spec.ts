import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/country/countryReducer";

const initialState = {
  countries: [],
};

describe("countryReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle API_FETCH_COUNTRIES", () => {
    expect(
      reducer(initialState, {
        type: "API_FETCH_COUNTRIES",
        payload: { iri: "/resources/42" },
      })
    ).to.deep.equal({
      countries: [],
      countriesListIsLoading: true,
    });
  });
  it("should handle API_FETCH_COUNTRIES_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "API_FETCH_COUNTRIES_SUCCESS",
        payload: {
          iri: "/resources/42",
          data: {
            "hydra:member": [
              { "@id": "/countries/69", name: "FRA" },
              { "@id": "/countries/70", name: "GER" },
            ],
          },
        },
      })
    ).to.deep.equal({
      countries: {
        "/countries/69": { "@id": "/countries/69", name: "FRA" },
        "/countries/70": { "@id": "/countries/70", name: "GER" },
      },
      countriesListIsLoading: false,
    });
  });
  it("should handle API_FETCH_COUNTRY_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "API_FETCH_COUNTRY_SUCCESS",
        payload: {
          iri: "/countries/999",
          data: { "@id": "/countries/999", name: "BRA" },
        },
      })
    ).to.deep.equal({
      countries: {
        "/countries/999": { "@id": "/countries/999", name: "BRA" },
      },
    });
  });
});
