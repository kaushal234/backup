import { describe, it } from "mocha";
import { expect } from "chai";
import {
  getCommentsMapping,
  getLogsMapping,
  isCreationPending,
} from "../../../selectors/activity/activitySelector";

const initialState: any = {
  activity: {
    comments: [],
    logs: [],
    pendingCommentCreations: {},
  },
};

describe("activitySelector", () => {
  describe("getLogsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        activity: {
          logs: {
            "/foo/1": [{ "@id": "/logs/1" }, { "@id": "/logs/2" }],
          },
        },
      };
      expect(getLogsMapping(initialState, "")).to.deep.equal(null);
      expect(getLogsMapping.recomputations()).to.equal(1);
      expect(
        getLogsMapping(state, "/foo/1")?.map((comment) => {
          return { ...comment, createdAt: null, updatedAt: null };
        })
      ).to.deep.equal([
        { "@id": "/logs/1", createdAt: null, updatedAt: null, index: 0 },
        { "@id": "/logs/2", createdAt: null, updatedAt: null, index: 1 },
      ]);
      expect(getLogsMapping.recomputations()).to.equal(2);
      getLogsMapping(state, "/foo/1");
      expect(getLogsMapping.recomputations()).to.equal(2);
    });
  });
  describe("getCommentsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        activity: {
          comments: {
            "/foo/1": [{ "@id": "/comments/1" }, { "@id": "/comments/2" }],
          },
        },
      };
      expect(getCommentsMapping(initialState, "")).to.deep.equal(null);
      expect(getCommentsMapping.recomputations()).to.equal(1);
      expect(
        getCommentsMapping(state, "/foo/1")?.map((comment) => {
          return { ...comment, createdAt: null, updatedAt: null };
        })
      ).to.deep.equal([
        { "@id": "/comments/1", createdAt: null, updatedAt: null, index: 0 },
        { "@id": "/comments/2", createdAt: null, updatedAt: null, index: 1 },
      ]);
      expect(getCommentsMapping.recomputations()).to.equal(2);
      getCommentsMapping(state, "/foo/1");
      expect(getCommentsMapping.recomputations()).to.equal(2);
    });
  });
  describe("isCreationPending", () => {
    it("should use memoization", () => {
      const state: any = {
        activity: {
          pendingCommentCreations: {
            "/foo/1": true,
          },
        },
      };
      expect(isCreationPending(initialState, "/foo/1")).to.be.false;
      expect(isCreationPending.recomputations()).to.equal(1);
      expect(isCreationPending(state, "/foo/1")).to.be.true;
      expect(isCreationPending.recomputations()).to.equal(2);
      isCreationPending(state, "/foo/2");
      expect(getCommentsMapping.recomputations()).to.equal(2);
    });
  });
});
