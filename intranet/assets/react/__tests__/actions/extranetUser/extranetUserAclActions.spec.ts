import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/extranetUser/extranetUserAclActions";

describe("extranetUserAclActions", () => {
  describe("writeExtranetUserAcl", () => {
    it("should create a create extranet user ACL action", () => {
      const expectedAction = {
        type: "EXTRANET_USER_CREATE_EXTRANET_USER_ACLS",
        payload: {
          url: "/sales/extranet_user_acls",
          form: "form",
          body: { foo: "bar" },
        },
      };
      expect(
        actions.writeExtranetUserAcl({ foo: "bar" }, "form")
      ).to.deep.equal(expectedAction);
    });
  });
});
