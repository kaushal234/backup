import { describe, it } from "mocha";
import { expect } from "chai";
import { customerRelationshipTeamFactory } from "../../../../model/form/customer_relationship_team/factory";

const dummyCustomerRelationshipTeam = {
  cuno: "foo",
  customer: { value: "/foo/1" },
  customerBusinessPartnerCode: { value: "foobar" },
  erpLocation: { value: "/bar/1" },
  partsLocation: { value: "/foobar/1" },
  serviceLocation: { value: "/barfoo/1" },
  salesRepresentative: { value: "/foofoo/1" },
  partsRepresentative: { value: "/barbar/1" },
  serviceRepresentative: { value: "/barfoobar/1" },
  bar: "bar",
};

describe("CRT factory", () => {
  it("should return a CRT", () => {
    expect({
      ...customerRelationshipTeamFactory({ ...dummyCustomerRelationshipTeam }),
    }).to.deep.equal({
      customerBusinessPartnerCode: "foobar",
      customer: "/foo/1",
      erpLocation: "/bar/1",
      partsLocation: "/foobar/1",
      serviceLocation: "/barfoo/1",
      salesRepresentative: "/foofoo/1",
      partsRepresentative: "/barbar/1",
      serviceRepresentative: "/barfoobar/1",
    });
  });
});

describe("CRT factory", () => {
  it("should return a CRT", () => {
    expect({
      ...customerRelationshipTeamFactory({
        ...dummyCustomerRelationshipTeam,
        id: 69,
      }),
    }).to.deep.equal({
      id: 69,
      customerBusinessPartnerCode: "foobar",
      customer: "/foo/1",
      erpLocation: "/bar/1",
      partsLocation: "/foobar/1",
      serviceLocation: "/barfoo/1",
      salesRepresentative: "/foofoo/1",
      partsRepresentative: "/barbar/1",
      serviceRepresentative: "/barfoobar/1",
    });
  });
});
