import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/userStory/userStoryRequestAction";
import {
  USER_STORY_WRITE,
  USER_STORY_EDIT,
  USER_STORY_GET,
  USER_STORY_UPDATE_STATUS,
  SPECIFICATION_GET,
  USER_STORY_DELETE,
  USER_STORY_RESET,
} from "../../../constants";

describe("userStoryRequestAction", () => {
  describe("When I want to create a user story", () => {
    it("should send the send the right payload to the saga", () => {
      const expectedDataAction = {
        type: USER_STORY_WRITE,
        payload: {
          url: "/mis/user_stories",
          body: { data: "user story data" },
          form: "user_story_form",
        },
      };
      const actualDataAction = actions.writeUserStory(
        { data: "user story data" },
        "user_story_form"
      );
      expect(actualDataAction).to.deep.equal(expectedDataAction);
    });
  });

  describe("When I want to edit a user story", () => {
    it("should send the send the right payload to the saga", () => {
      const userStoryId = 1;
      const expectedDataAction = {
        type: USER_STORY_EDIT,
        payload: {
          url: `/mis/user_stories/${userStoryId}`,
          body: { data: "user story data" },
          form: "user_story_form",
        },
      };
      const actualDataAction = actions.writeUserStory(
        { id: userStoryId, data: "user story data" },
        "user_story_form"
      );
      expect(actualDataAction).to.deep.equal(expectedDataAction);
    });
  });

  describe("When I want to get the collection of user stories from the specification", () => {
    it("should send the send the right payload to the saga", () => {
      const specificationId = 1;
      const expectedDataAction = {
        type: SPECIFICATION_GET,
        payload: {
          url: `/mis/specifications/${specificationId}`,
        },
      };
      const actualDataAction = actions.getSpecification(specificationId);
      expect(actualDataAction).to.deep.equal(expectedDataAction);
    });
  });

  describe("When I want to get a user story", () => {
    it("should send the send the right payload to the saga", () => {
      const userStoryId = 1;
      const expectedDataAction = {
        type: USER_STORY_GET,
        payload: {
          url: `/mis/user_stories/${userStoryId}`,
        },
      };
      const actualDataAction = actions.getUserStory(userStoryId);
      expect(actualDataAction).to.deep.equal(expectedDataAction);
    });
  });

  describe("When I want to delete a user story", () => {
    it("should send the send the right payload to the saga", () => {
      const userStoryId = 1;
      const expectedDataAction = {
        type: USER_STORY_DELETE,
        payload: {
          url: `/mis/user_stories/${userStoryId}`,
        },
      };
      const actualDataAction = actions.deleteUserStory(userStoryId);
      expect(actualDataAction).to.deep.equal(expectedDataAction);
    });
  });

  describe("When I want to reset the list of user story", () => {
    it("should send the send the right type to the reducer", () => {
      const expectedDataAction = {
        type: USER_STORY_RESET,
      };
      const actualDataAction = actions.getUserStoryReset();
      expect(actualDataAction).to.deep.equal(expectedDataAction);
    });
  });

  describe("When I want to reset the list of user story", () => {
    it("should send the send the right type to the reducer", () => {
      const expectedDataAction = {
        type: USER_STORY_RESET,
      };
      const actualDataAction = actions.getUserStoryReset();
      expect(actualDataAction).to.deep.equal(expectedDataAction);
    });
  });

  describe("updateStatusUserStory", () => {
    it("should handle PENDING status correctly", () => {
      const userStoryId = 1;
      const expectedAction = {
        type: USER_STORY_UPDATE_STATUS,
        payload: {
          url: `mis/user_stories/${userStoryId}/status`,
          body: { status: "PLANNED" },
        },
      };
      const actualAction = actions.updateStatusUserStory(
        userStoryId,
        "PENDING"
      );
      expect(actualAction).to.deep.equal(expectedAction);
    });

    it("should handle PLANNED status correctly", () => {
      const userStoryId = 1;
      const expectedAction = {
        type: USER_STORY_UPDATE_STATUS,
        payload: {
          url: `mis/user_stories/${userStoryId}/status`,
          body: { status: "DEVELOPMENT" },
        },
      };
      const actualAction = actions.updateStatusUserStory(
        userStoryId,
        "PLANNED"
      );
      expect(actualAction).to.deep.equal(expectedAction);
    });

    it("should handle EDIT status correctly", () => {
      const userStoryId = 1;
      const expectedAction = {
        type: USER_STORY_UPDATE_STATUS,
        payload: {
          url: `mis/user_stories/${userStoryId}/status`,
          body: { status: "HAS BEEN EDITED" },
        },
      };
      const actualAction = actions.updateStatusUserStory(userStoryId, "EDIT");
      expect(actualAction).to.deep.equal(expectedAction);
    });

    it("should handle HAS BEEN EDITED status correctly", () => {
      const userStoryId = 1;
      const expectedAction = {
        type: USER_STORY_UPDATE_STATUS,
        payload: {
          url: `mis/user_stories/${userStoryId}/status`,
          body: { status: "VALIDATED" },
        },
      };
      const actualAction = actions.updateStatusUserStory(
        userStoryId,
        "HAS BEEN EDITED"
      );
      expect(actualAction).to.deep.equal(expectedAction);
    });

    it("should handle DEVELOPMENT status correctly", () => {
      const userStoryId = 1;
      const expectedAction = {
        type: USER_STORY_UPDATE_STATUS,
        payload: {
          url: `mis/user_stories/${userStoryId}/status`,
          body: { status: "TESTING" },
        },
      };
      const actualAction = actions.updateStatusUserStory(
        userStoryId,
        "DEVELOPMENT"
      );
      expect(actualAction).to.deep.equal(expectedAction);
    });

    it("should handle TESTING status correctly", () => {
      const userStoryId = 1;
      const expectedAction = {
        type: USER_STORY_UPDATE_STATUS,
        payload: {
          url: `mis/user_stories/${userStoryId}/status`,
          body: { status: "VALIDATED" },
        },
      };
      const actualAction = actions.updateStatusUserStory(
        userStoryId,
        "TESTING"
      );
      expect(actualAction).to.deep.equal(expectedAction);
    });

    it("should handle default case correctly", () => {
      const userStoryId = 1;
      const expectedAction = {
        type: USER_STORY_UPDATE_STATUS,
        payload: {
          url: `mis/user_stories/${userStoryId}/status`,
          body: {},
        },
      };
      const actualAction = actions.updateStatusUserStory(
        userStoryId,
        "UNKNOWN_STATUS"
      );
      expect(actualAction).to.deep.equal(expectedAction);
    });
  });
});
