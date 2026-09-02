import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/extranetUser/extranetUserActions";

describe("extranetUserActions", () => {
  describe("fetchExtranetUsersList", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "EXTRANET_USER_FETCH_EXTRANET_USERS",
        payload: {
          request: {
            url: "/sales/extranet_users?order[lastname]=asc&hidden=0&extranetUserProfile.archived=0&normalization_groups_override[]=extranet_user_list&pagination=false&q=test",
          },
        },
      };
      expect(actions.fetchExtranetUsersList("test")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("fetchExtranetUsersHierarchyList", () => {
    it("should create a fetch hierarchy action", () => {
      const expectedAction = {
        type: "EXTRANET_USER_FETCH_EXTRANET_USERS",
        payload: {
          request: {
            url: "/sales/extranet_users?customer_hierarchy=/foo/1",
          },
        },
      };
      expect(actions.fetchExtranetUsersHierarchyList("/foo/1")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("fetchContact", () => {
    it("should create a fetch contact action", () => {
      const expectedAction = {
        type: "SPR_FETCH_CONTACT",
        form: "form",
        index: 2,
        payload: {
          request: {
            url: "/foo/1",
          },
        },
      };
      expect(actions.fetchContact("/foo/1", "form", 2)).to.deep.equal(
        expectedAction
      );
    });
  });
});
