import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/incoterm/termsOfDeliveryActions";

describe("termsOfDeliveryActions", () => {
  describe("fetchTermsOfDeliveries", () => {
    it("should create an action to fetch terms of delivery", () => {
      const url = "/sales/incoterms";
      const expectedAction = {
        type: "INCOTERM_FETCH_TERMS_OF_DELIVERIES",
        payload: {
          request: {
            url,
          },
        },
      };
      expect(actions.fetchTermsOfDeliveries()).to.deep.equal(expectedAction);
    });
  });
});
