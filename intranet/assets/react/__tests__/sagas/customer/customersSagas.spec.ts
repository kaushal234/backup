import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill } from "redux-form";
import { fetchAPI } from "../../../utils/api";
import { client } from "../../../store";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import {
  createCustomerInForm,
  fetchSalesCustomer,
  fetchSalesCustomers,
  updateCustomerWatchList,
} from "../../../actions/customer/customersActions";
import {
  callFetchSalesCustomers,
  callCreateCustomerInForm,
  callUpdateCustomerWatchList,
  callFetchSalesCustomer,
} from "../../../sagas/customer/customersSagas";

describe("customersSagas", () => {
  describe("callCreateCustomerInForm", () => {
    describe("Successful calls", () => {
      const generator = callCreateCustomerInForm(
        createCustomerInForm({ foo: "bar" }, "input", "form")
      );

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/sales/customers", { foo: "bar" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({ data: { "@id": "/foo/1", name: "foo" } }).value
        ).to.deep.equal(
          put(
            APICallSuccess("SALES_CREATE_CUSTOMER_FORM", {
              data: { "@id": "/foo/1", name: "foo" },
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "input", { value: "/foo/1", label: "foo" }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callCreateCustomerInForm(
        createCustomerInForm({ foo: "bar" }, "input", "form")
      );

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/sales/customers", { foo: "bar" })
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            violations: [
              {
                propertyPath: "pathLeChien",
                message: "in a bottle",
              },
            ],
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed("SALES_CREATE_CUSTOMER_FORM", {
              status: 400,
              data: {
                violations: [
                  {
                    propertyPath: "pathLeChien",
                    message: "in a bottle",
                  },
                ],
              },
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callFetchCustomers", () => {
    describe("Successful calls", () => {
      const generator = callFetchSalesCustomers(
        fetchSalesCustomers("test", "input")
      );

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            fetchAPI,
            "/sales/customers?normalization_groups_override[]=customer_list&order[name]=asc&q=test&hidden=0"
          )
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({
            data: { "hydra:member": ["OMG", "LOL"] },
            name: "input",
          }).value
        ).to.deep.equal(
          put(
            APICallSuccess("SALES_FETCH_CUSTOMERS", {
              data: { "hydra:member": ["OMG", "LOL"] },
              name: "input",
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callFetchSalesCustomers(fetchSalesCustomers("test"));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            fetchAPI,
            "/sales/customers?normalization_groups_override[]=customer_list&order[name]=asc&q=test&hidden=0"
          )
        );
      });
      const error = { response: "test" };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("SALES_FETCH_CUSTOMERS", "test"))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callFetchCustomer", () => {
    describe("Successful calls", () => {
      const generator = callFetchSalesCustomer(fetchSalesCustomer("/foo/1"));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(call(fetchAPI, "/foo/1"));
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({
            data: {
              foo: "bar",
              mainSalesRepresentative: {
                asm: {
                  "@id": "/bar/1",
                  lastname: "Carle",
                  firstname: "Philippe",
                },
              },
            },
          }).value
        ).to.deep.equal(
          put(
            APICallSuccess("SALES_FETCH_CUSTOMER", {
              data: {
                foo: "bar",
                mainSalesRepresentative: {
                  asm: {
                    "@id": "/bar/1",
                    lastname: "Carle",
                    firstname: "Philippe",
                  },
                },
              },
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill("customer_relationship_team_form", "salesRepresentative", {
              value: "/bar/1",
              label: "Carle Philippe",
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callFetchSalesCustomer(fetchSalesCustomer("/foo/1"));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(call(fetchAPI, "/foo/1"));
      });
      const error = { response: "test" };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("SALES_FETCH_CUSTOMER", "test"))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callUpdateCustomerWatchList", () => {
    describe("Successful call for true", () => {
      const generator = callUpdateCustomerWatchList(
        updateCustomerWatchList(
          "/sales/customers/31",
          12,
          true,
          "baaaad customer",
          "form"
        )
      );

      it("should first autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "customers[12].saving", 1))
        );
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/sales/customers/31/watch_list", {
            watchList: true,
            watchListReason: "baaaad customer",
          })
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({ data: { "@id": "/foo/1", name: "foo" } }).value
        ).to.deep.equal(
          put(
            APICallSuccess("SALES_UPDATE_CUSTOMER_WATCH_LIST", {
              data: { "@id": "/foo/1", name: "foo" },
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "customers[12].saving", 0))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Successful call for false", () => {
      const generator = callUpdateCustomerWatchList(
        updateCustomerWatchList("/sales/customers/31", 12, false, null, "form")
      );

      it("should first autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "customers[12].deleting", 1))
        );
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/sales/customers/31/watch_list", {
            watchList: false,
            watchListReason: null,
          })
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({ data: { "@id": "/foo/1", name: "foo" } }).value
        ).to.deep.equal(
          put(
            APICallSuccess("SALES_UPDATE_CUSTOMER_WATCH_LIST", {
              data: { "@id": "/foo/1", name: "foo" },
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "customers[12].softDeleted", 1))
        );
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "customers[12].deleting", 0))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callUpdateCustomerWatchList(
        updateCustomerWatchList("/sales/customers/31", 12, false, null, "form")
      );

      it("should first autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "customers[12].deleting", 1))
        );
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/sales/customers/31/watch_list", {
            watchList: false,
            watchListReason: null,
          })
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            violations: [
              {
                propertyPath: "pathLeChien",
                message: "in a bottle",
              },
            ],
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed("SALES_UPDATE_CUSTOMER_WATCH_LIST", {
              status: 400,
              data: {
                violations: [
                  {
                    propertyPath: "pathLeChien",
                    message: "in a bottle",
                  },
                ],
              },
            })
          )
        );
      });
      it("should first autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "customers[12].deleting", 0))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
