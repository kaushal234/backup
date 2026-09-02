import {
  SALES_CLEAR_CUSTOMERS,
  SALES_CREATE_CUSTOMER_FORM,
  SALES_FETCH_CUSTOMER,
  SALES_FETCH_CUSTOMERS,
  SALES_UPDATE_CUSTOMER_WATCH_LIST,
} from "../../constants";

export function fetchSalesCustomer(iri: any, type = SALES_FETCH_CUSTOMER) {
  return {
    type,
    payload: {
      request: {
        url: iri,
      },
    },
  };
}

export function fetchSalesCustomers(
  search: any,
  name?: any,
  showHidden = false,
  showActive = false
) {
  return {
    type: SALES_FETCH_CUSTOMERS,
    payload: {
      request: {
        url: `/sales/customers?normalization_groups_override[]=customer_list&order[name]=asc&q=${search}${
          !showHidden ? "&hidden=0" : ""
        }${showActive ? "&active=1" : ""}`,
        name,
      },
    },
  };
}

export function createCustomerInForm(customer: any, inputName: any, form: any) {
  return {
    type: SALES_CREATE_CUSTOMER_FORM,
    form,
    inputName,
    payload: {
      url: `/sales/customers`,
      body: customer,
    },
  };
}

export function clearSalesCustomers() {
  return {
    type: SALES_CLEAR_CUSTOMERS,
  };
}

export function updateCustomerWatchList(
  customerIri: any,
  customerIndex: any,
  watchList: any,
  watchListReason: any,
  form: any
) {
  return {
    type: SALES_UPDATE_CUSTOMER_WATCH_LIST,
    customerIri,
    customerIndex,
    form,
    payload: {
      url: `${customerIri}/watch_list`,
      body: {
        watchList,
        watchListReason,
      },
    },
  };
}
