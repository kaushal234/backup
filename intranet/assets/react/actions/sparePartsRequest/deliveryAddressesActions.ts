import { SPR_FETCH_DELIVERY_ADDRESSES } from "../../constants";

export function fetchDeliveryAddresses(
  airport: any,
  customers: any,
  discriminator = "discr"
) {
  let url = `parts/spare_parts_request_delivery_addresses?archived=0&airport=${airport}`;
  (customers ?? []).forEach((iri: any) => {
    url += `&contact.extranetUserProfile.customer[]=${iri}`;
  });
  return {
    type: SPR_FETCH_DELIVERY_ADDRESSES,
    discriminator,
    payload: {
      request: {
        url,
      },
    },
  };
}
