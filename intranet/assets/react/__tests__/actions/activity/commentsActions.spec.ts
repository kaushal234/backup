import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/activity/commentsActions";

describe("commentsActions", () => {
  describe("createComment", () => {
    it("should create a create action", () => {
      const expectedAction = {
        type: "ACTIVITY_CREATE_COMMENT",
        payload: {
          request: {
            url: "/comments",
            body: {
              resource: "/resource/1",
              message: "my message",
              public: false,
              metadata: { my_key: "my_value" },
              file: undefined,
            },
          },
        },
      };
      expect(
        actions.createComment("/resource/1", "my message", undefined, false, {
          my_key: "my_value",
        })
      ).to.deep.equal(expectedAction);
    });
    it("should create a create action with discriminator", () => {
      const expectedAction = {
        type: "ACTIVITY_CREATE_COMMENT",
        payload: {
          request: {
            url: "/comments",
            body: {
              resource: "/resource/1",
              message: "my message",
              file: undefined,
              public: true,
              metadata: { my_key: "my_value" },
            },
          },
        },
      };
      expect(
        actions.createComment("/resource/1", "my message", undefined, true, {
          my_key: "my_value",
        })
      ).to.deep.equal(expectedAction);
    });
  });
  describe("getComments", () => {
    it("should create a fetch comments action", () => {
      const expectedAction = {
        type: "ACTIVITY_FETCH_COMMENTS",
        payload: {
          url: "/comments?normalization_groups[]=file:light&normalizationGroups[]=activity_position&resource=/resources/42&pagination=false&extraComment=true",
          iri: "/resources/42",
        },
      };
      expect(actions.getComments("/resources/42")).to.deep.equal(
        expectedAction
      );
    });
  });
});
