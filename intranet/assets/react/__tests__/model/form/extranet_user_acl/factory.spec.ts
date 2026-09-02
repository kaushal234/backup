import { describe, it } from "mocha";
import { expect } from "chai";
import { extranetUserAclFactory } from "../../../../model/form/extranetUserAcl/factory";

const dummyExtranetUserAcl = {
  id: 59,
  extranetUser: {
    value: "/foo/1",
  },
  extranetUserGroups: [{ value: "/bar/1" }, { value: "/bar/2" }],
  deliveryAddress: "foobar",
  bar: "bar",
};
describe("ExtranetUser ACL factory", () => {
  it("should return an ExtranetUserAcl", () => {
    expect({
      ...extranetUserAclFactory({ ...dummyExtranetUserAcl }, 1),
    }).to.deep.equal({
      crt: "/sales/customer_relationship_teams/59",
      extranetUser: "/foo/1",
      extranetUserGroup: "/bar/2",
      cDel: "foobar",
    });
  });
});
