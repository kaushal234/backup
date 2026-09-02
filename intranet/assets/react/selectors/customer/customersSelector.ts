import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getSalesCustomers = (state: RootState) => {
  return state.customer.customers;
};

const getSelectedCustomerBusinessPartnerCodes = (state: RootState) => {
  return state.crt.selectedCustomer?.inforLnBusinessPartnerCodes;
};

const buildNameOption = (customer: any) => ({
  value: _.get(customer, "@id"),
  label:
    customer.status === "APPROVED"
      ? customer.name
      : `${customer.name} (status: ${customer.status})`,
});

export const getSalesCustomersSelectMapping = createSelector(
  [getSalesCustomers],
  (customers) => {
    if (!customers) {
      return [];
    }
    return Object.values(customers).map((customer) =>
      buildNameOption(customer)
    );
  }
);

export const getSalesCustomersMapping = createSelector(
  [getSalesCustomers],
  (customers) => {
    if (!customers) {
      return [];
    }
    return Object.values(customers);
  }
);

export const getSelectedCustomerBusinessPartnerCodesMapping = createSelector(
  [getSelectedCustomerBusinessPartnerCodes],
  (selectedCustomerBusinessPartnerCodes) => {
    if (!selectedCustomerBusinessPartnerCodes) {
      return [];
    }

    const options = selectedCustomerBusinessPartnerCodes.map(
      (businessPartnerCode: any) => {
        return {
          value: businessPartnerCode.trim(),
          label: businessPartnerCode.trim(),
        };
      }
    );

    return options;
  }
);
