import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/user/userActions";

describe("userAction", () => {
  describe("fetchAsyncUsers", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "USER_FETCH_USERS",
        payload: {
          request: {
            url: "/people?order[lastname]=asc&hidden=0&disabled=0&normalization_groups_override[]=people_list&pagination=false&q=test",
          },
        },
      };
      expect(actions.fetchUsersAsyncList("test")).to.deep.equal(expectedAction);
    });
  });
  describe("fetchAsyncUsersWithFilter", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "USER_FETCH_USERS",
        payload: {
          request: {
            url: "/people?order[lastname]=asc&hidden=0&disabled=0&normalization_groups_override[]=people_list&pagination=false&q=test&excludeGroup=FOO",
          },
        },
      };
      expect(actions.fetchUsersAsyncList("test", "FOO")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("fetchRequestTypes", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "USER_FETCH_USERS",
        payload: {
          request: {
            url: "/people?order[lastname]=asc&hidden=0&disabled=0&normalization_groups_override[]=people_list&pagination=false",
          },
        },
      };
      expect(actions.fetchUsersList()).to.deep.equal(expectedAction);
    });
  });
  describe("fetchConnectedUser", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "USER_FETCH_CONNECTED_USER",
        payload: {
          request: {
            url: "/me",
          },
        },
      };
      expect(actions.fetchConnectedUser()).to.deep.equal(expectedAction);
    });
  });
  describe("fetchConnectedUser", () => {
    it("should create a fetch action with a specific action", () => {
      const expectedAction = {
        type: "USER_FETCH_CONNECTED_USER_ERP_FOR_SPQ",
        payload: {
          request: {
            url: "/me",
          },
        },
      };
      expect(
        actions.fetchConnectedUser("USER_FETCH_CONNECTED_USER_ERP_FOR_SPQ")
      ).to.deep.equal(expectedAction);
    });
  });
});
