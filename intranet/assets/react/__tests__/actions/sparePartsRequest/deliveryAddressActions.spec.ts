import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/sparePartsRequest/deliveryAddressesActions";

describe("deliveryAddressesActions", () => {
  describe("fetchDeliveryAddresses", () => {
    it("should create a fetch delivery addresses action", () => {
      const expectedAction = {
        type: "SPR_FETCH_DELIVERY_ADDRESSES",
        discriminator: "discr",
        payload: {
          request: {
            url: "parts/spare_parts_request_delivery_addresses?archived=0&airport=/airports/1&contact.extranetUserProfile.customer[]=/sales/customers/1&contact.extranetUserProfile.customer[]=/sales/customers/2",
          },
        },
      };
      expect(
        actions.fetchDeliveryAddresses("/airports/1", [
          "/sales/customers/1",
          "/sales/customers/2",
        ])
      ).to.deep.equal(expectedAction);
    });
  });
});
