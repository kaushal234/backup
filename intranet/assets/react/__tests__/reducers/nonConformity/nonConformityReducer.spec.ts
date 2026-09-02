import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/nonConformity/nonConformityReducer";

const initialState = {
  nonConformities: [],
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

describe("nonConformityReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle NON_CONFORMITY_FETCH_NON_CONFORMITIES_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "NON_CONFORMITY_FETCH_NON_CONFORMITIES_SUCCESS",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      nonConformities: [
        { "@id": "/foo/1" },
        { "@id": "/foo/2" },
        { "@id": "/foo/3" },
      ],
      nonConformitiesListIsLoading: false,
    });
  });
  it("should handle NON_CONFORMITY_FETCH_NON_CONFORMITIES", () => {
    expect(
      reducer(initialState, { type: "NON_CONFORMITY_FETCH_NON_CONFORMITIES" })
    ).to.deep.equal({
      nonConformities: [],
      nonConformitiesListIsLoading: true,
    });
  });
});
