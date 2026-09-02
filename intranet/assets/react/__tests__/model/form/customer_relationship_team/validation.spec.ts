import { describe, it } from "mocha";
import { expect } from "chai";
import Translator from "bazinga-translator";
import validate from "../../../../model/form/customer_relationship_team/validation";

describe("customer relationship team validation", () => {
  const defaultErrors = {
    customer: Translator.trans("customer_relationship_team.errors.customer"),
    erpLocation: Translator.trans(
      "customer_relationship_team.errors.erpLocation"
    ),
    partsLocation: Translator.trans(
      "customer_relationship_team.errors.partsLocation"
    ),
    serviceLocation: Translator.trans(
      "customer_relationship_team.errors.serviceLocation"
    ),
    salesRepresentative: Translator.trans(
      "customer_relationship_team.errors.salesRepresentative"
    ),
    partsRepresentative: Translator.trans(
      "customer_relationship_team.errors.partsRepresentative"
    ),
    serviceRepresentative: Translator.trans(
      "customer_relationship_team.errors.serviceRepresentative"
    ),
  };
  it("Should have default errors", () => {
    expect(validate({})).to.deep.equal(defaultErrors);
  });

  it("Should throw validation error when no groups nor extranet user selected", () => {
    expect(validate({ id: 69 })).to.deep.equal({
      extranetUserGroups: Translator.trans(
        "customer_relationship_team.errors.extranet_user_group"
      ),
      extranetUser: Translator.trans(
        "customer_relationship_team.errors.extranet_user"
      ),
    });
  });
});
