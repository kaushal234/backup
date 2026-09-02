import { describe, it } from "mocha";
import { expect } from "chai";
import tagReducer from "../../../reducers/tag/tagReducer";

const initialState = {
  tags: [],
  tagsIsLoading: false,
  error: null,
};

describe("tagReducer", () => {
  it("should return the initial state", () => {
    expect(tagReducer(undefined, { type: "@@INIT" })).to.deep.equal(
      initialState
    );
  });

  it("should handle TAG_FETCH_LIST", () => {
    expect(tagReducer(initialState, { type: "TAG_FETCH_LIST" })).to.deep.equal({
      ...initialState,
      tagsIsLoading: true,
      error: null,
    });
  });

  it("should handle TAG_FETCH_LIST_SUCCESS", () => {
    const action = {
      type: "TAG_FETCH_LIST_SUCCESS",
      payload: {
        data: {
          "hydra:member": [
            { "@id": "/tags/1", name: "Tag 1" },
            { "@id": "/tags/2", name: "Tag 2" },
          ],
        },
      },
    };
    expect(tagReducer(initialState, action)).to.deep.equal({
      ...initialState,
      tags: {
        "/tags/1": { "@id": "/tags/1", name: "Tag 1" },
        "/tags/2": { "@id": "/tags/2", name: "Tag 2" },
      },
      tagsIsLoading: false,
      error: null,
    });
  });

  it("should handle TAG_FETCH_LIST_FAILED", () => {
    const action = {
      type: "TAG_FETCH_LIST_FAILED",
      payload: { error: "Failed to fetch tags" },
    };
    expect(tagReducer(initialState, action)).to.deep.equal({
      ...initialState,
      tagsIsLoading: false,
      error: { error: "Failed to fetch tags" },
    });
  });
});
