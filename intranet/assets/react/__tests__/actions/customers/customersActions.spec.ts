import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/customer/customersActions";

describe("customersActions", () => {
  describe("fetchCustomer", () => {
    it("should create a fetch customer action", () => {
      const expectedAction = {
        type: "SALES_FETCH_CUSTOMER",
        payload: {
          request: {
            url: "/sales/customers/1",
          },
        },
      };
      expect(actions.fetchSalesCustomer("/sales/customers/1")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("fetchCustomers", () => {
    it("should create a fetch customers action", () => {
      const expectedAction = {
        type: "SALES_FETCH_CUSTOMERS",
        payload: {
          request: {
            url: "/sales/customers?normalization_groups_override[]=customer_list&order[name]=asc&q=AIR&hidden=0",
            name: "buyer",
          },
        },
      };
      expect(actions.fetchSalesCustomers("AIR", "buyer")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("fetchCustomers", () => {
    it("should create a fetch customers active action", () => {
      const expectedAction = {
        type: "SALES_FETCH_CUSTOMERS",
        payload: {
          request: {
            url: "/sales/customers?normalization_groups_override[]=customer_list&order[name]=asc&q=AIR&hidden=0&active=1",
            name: "buyer",
          },
        },
      };
      expect(
        actions.fetchSalesCustomers("AIR", "buyer", false, true)
      ).to.deep.equal(expectedAction);
    });
  });
  describe("createCustomerInForm", () => {
    it("should create a create customers action", () => {
      const fakeCustomer = {
        name: "AIR",
      };
      const expectedAction = {
        type: "SALES_CREATE_CUSTOMER_FORM",
        form: "formName",
        inputName: "inputName",
        payload: {
          url: "/sales/customers",
          body: fakeCustomer,
        },
      };
      expect(
        actions.createCustomerInForm(fakeCustomer, "inputName", "formName")
      ).to.deep.equal(expectedAction);
    });
  });
  describe("clearSalesCustomers", () => {
    it("should create a clear customers action", () => {
      const expectedAction = {
        type: "SALES_CLEAR_CUSTOMERS",
      };
      expect(actions.clearSalesCustomers()).to.deep.equal(expectedAction);
    });
  });
  describe("updateCustomerWatchList", () => {
    it("should create an update customer watch list action", () => {
      const expectedAction = {
        type: "SALES_UPDATE_CUSTOMER_WATCH_LIST",
        customerIri: "/sales/customers/42",
        customerIndex: 12,
        form: "à fond la forme",
        payload: {
          url: "/sales/customers/42/watch_list",
          body: {
            watchList: true,
            watchListReason: "test",
          },
        },
      };
      expect(
        actions.updateCustomerWatchList(
          "/sales/customers/42",
          12,
          true,
          "test",
          "à fond la forme"
        )
      ).to.deep.equal(expectedAction);
    });
  });
});
