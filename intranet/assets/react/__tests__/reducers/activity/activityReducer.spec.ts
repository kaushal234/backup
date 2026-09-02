import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/activity/activityReducer";

const initialState = {
  logs: {},
  comments: {},
  pendingCommentCreations: {},
};

describe("activityReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle ACTIVITY_FETCH_LOGS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "ACTIVITY_FETCH_LOGS_SUCCESS",
        payload: {
          iri: "/resources/42",
          data: { "hydra:member": [{ test: "log" }] },
        },
      })
    ).to.deep.equal({
      logs: {
        "/resources/42": [{ test: "log" }],
      },
      comments: {},
      pendingCommentCreations: {},
    });
  });
  it("should handle ACTIVITY_FETCH_COMMENTS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "ACTIVITY_FETCH_COMMENTS_SUCCESS",
        payload: {
          iri: "/resources/42",
          data: { "hydra:member": [{ test: "comment" }] },
        },
      })
    ).to.deep.equal({
      comments: {
        "/resources/42": [{ test: "comment" }],
      },
      logs: {},
      pendingCommentCreations: {},
    });
  });
  it("should handle ACTIVITY_CREATE_COMMENT", () => {
    expect(
      reducer(initialState, {
        type: "ACTIVITY_CREATE_COMMENT",
        payload: { request: { body: { resource: "/resources/404" } } },
      })
    ).to.deep.equal({
      logs: {},
      comments: {},
      pendingCommentCreations: { "/resources/404": true },
    });
  });
  it("should handle ACTIVITY_CREATE_COMMENT_SUCCESS", () => {
    const existingState = {
      logs: {},
      comments: {},
      pendingCommentCreations: { "/resources/43": true },
    };
    expect(
      reducer(existingState, {
        type: "ACTIVITY_CREATE_COMMENT_SUCCESS",
        payload: {
          iri: "/resources/43",
          data: { "@id": "/comments/12", resource: "/resources/43" },
        },
      })
    ).to.deep.equal({
      logs: {},
      comments: {
        "/resources/43": [
          { "@id": "/comments/12", position: 1, resource: "/resources/43" },
        ],
      },
      pendingCommentCreations: {},
    });
  });
});
