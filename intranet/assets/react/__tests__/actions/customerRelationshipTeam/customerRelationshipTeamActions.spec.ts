import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/customerRelationshipTeam/customerRelationshipTeamActions";

describe("customerRelationshipTeamActions", () => {
  describe("writeCustomerRelationshipTeam", () => {
    it("should create an edit CRT action", () => {
      const expectedAction = {
        type: "SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM",
        payload: {
          url: "/sales/customer_relationship_teams/69",
          form: "form",
          body: { id: 69, foo: "bar" },
        },
      };
      expect(
        actions.writeCustomerRelationshipTeam({ id: 69, foo: "bar" }, "form")
      ).to.deep.equal(expectedAction);
    });
    it("should create a write CRT action", () => {
      const expectedAction = {
        type: "SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM",
        payload: {
          url: "/sales/customer_relationship_teams",
          form: "form",
          body: { foo: "bar" },
        },
      };
      expect(
        actions.writeCustomerRelationshipTeam({ foo: "bar" }, "form")
      ).to.deep.equal(expectedAction);
    });
  });
});
