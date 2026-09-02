import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getDeliveryAddresses = (state: RootState, discriminator = "discr") => {
  return state.spr.deliveryAddresses[discriminator];
};

const buildLabel = (deliveryAddress: any) => {
  const values: Array<any> = [];
  const fields = [
    "street1",
    "street2",
    "postalCode",
    "city",
    "town",
    "state",
    "country",
  ];
  fields.forEach((value) => {
    if (deliveryAddress.address[value]) {
      values.push(deliveryAddress.address[value]);
    }
  });

  return values;
};

const buildNameOption = (deliveryAddress: any) => ({
  ...deliveryAddress,
  value: _.get(deliveryAddress, "@id"),
  label: buildLabel(deliveryAddress).join(", "),
});

export const getDeliveryAddressesMapping = createSelector(
  [getDeliveryAddresses],
  (deliveryAddresses) => {
    if (!deliveryAddresses) {
      return [];
    }
    return deliveryAddresses.map((deliveryAddress: any) =>
      buildNameOption(deliveryAddress)
    );
  }
);
