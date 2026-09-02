import { createSelector } from "reselect";
import { RootState } from "../../store";

const getCustomers = (state: RootState) => {
  return state.erp.customers;
};

export const getCustomersMapping = createSelector(
  [getCustomers],
  (customers) => {
    if (!customers) {
      return [];
    }
    return customers.map(({ name, cuno }: any) => ({
      value: cuno,
      name,
      label: `${cuno} - ${name}`,
    }));
  }
);

const getCustomerDeliveryAddresses = (state: RootState) => {
  return state.erp.customer.deliveryAddresses;
};

export const getCustomerDeliveryAddressesMapping = createSelector(
  [getCustomerDeliveryAddresses],
  (addresses) => {
    if (!addresses) {
      return [];
    }
    const addressesList = addresses.map((address: any) => ({
      value: address.cdel,
      label: [
        address.cdel,
        address.name,
        address.nameExtra,
        address.address,
        address.addressExtra,
        address.city,
        address.cityExtra,
        address.country,
      ]
        .filter((val) => {
          return val;
        })
        .join(" "),
    }));
    addressesList.push({ value: "oth", label: "Use another address" });
    return addressesList;
  }
);

export const getCustomerDeliveryAddress = (state: RootState, value: any) => {
  let addresses = state.erp.customer.deliveryAddresses || [];
  addresses = addresses.filter((address: any) => address.cdel === value);
  if (addresses.length > 0) {
    return addresses[0];
  }
  return [];
};

const getBusinessPartners = (state: RootState) => {
  return state.erp.businessPartners;
};

export const getBusinessPartnersMapping = createSelector(
  [getBusinessPartners],
  (businessPartners) => {
    if (!businessPartners) {
      return [];
    }
    return businessPartners.map(({ code, name }: any) => ({
      value: code,
      name,
      label: `${code} - ${name}`,
    }));
  }
);

export const getBusinessPartnersMappingWithNameAsValue = createSelector(
  [getBusinessPartners],
  (businessPartners) => {
    if (!businessPartners) {
      return [];
    }
    return businessPartners.map(({ code, name }: any) => ({
      value: name,
      label: `${code} - ${name}`,
    }));
  }
);

const getParts = (state: RootState) => {
  return state.erp.parts;
};

const buildPartNameOption = (part: any) => ({
  ...part,
  value: part.item,
  label: `${part.item} - ${part.itemDescription || "undefined"}`,
});

export const getPartsListMapping = createSelector([getParts], (parts) => {
  if (!parts) {
    return [];
  }
  return parts.map((part: any) => buildPartNameOption(part));
});
