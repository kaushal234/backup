import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import { client } from "../../../store";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import { generateFormErrors } from "../../../utils/api";
import { writeCustomerRelationshipTeam } from "../../../actions/customerRelationshipTeam/customerRelationshipTeamActions";
import {
  callCreateCustomerRelationshipTeam,
  callEditCustomerRelationshipTeam,
} from "../../../sagas/customerRelationshipTeam/customerRelationshipTeamSagas";

describe("customerRelationshipTeamSagas", () => {
  describe("callCreateCustomerRelationshipTeam", () => {
    describe("Successful calls", () => {
      const generator = callCreateCustomerRelationshipTeam(
        writeCustomerRelationshipTeam({ foo: "bar" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/sales/customer_relationship_teams", {
            foo: "bar",
          })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(
            APICallSuccess("SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM", {
              data: { id: 15 },
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "id", 15))
        );
      });
      it("should then stop form submission", () => {
        expect(generator.next({ form: "form_name" }).value).to.deep.equal(
          put(stopSubmit("form", {}))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callCreateCustomerRelationshipTeam(
        writeCustomerRelationshipTeam({ foo: "bar" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/sales/customer_relationship_teams", {
            foo: "bar",
          })
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            "hydra:description": "ERREUR",
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed(
              "SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM",
              error.response
            )
          )
        );
      });
      it("should then generate errors", () => {
        expect(generator.next().value).to.deep.equal(
          call(generateFormErrors, error)
        );
      });
      it("should then autofill form", () => {
        expect(
          generator.next({ _error: "Internal Server Error." }).value
        ).to.deep.equal(put(autofill("form", "errorMessage", "ERREUR")));
      });
      it("should then stop form submission", () => {
        expect(
          generator.next({ _error: "Internal Server Error." }).value
        ).to.deep.equal(
          put(stopSubmit("form", { _error: "Internal Server Error." }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callEditCustomerRelationshipTeam", () => {
    describe("Successful calls", () => {
      const generator = callEditCustomerRelationshipTeam(
        writeCustomerRelationshipTeam({ id: 15, foo: "bar" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/sales/customer_relationship_teams/15", {
            id: 15,
            foo: "bar",
          })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(
            APICallSuccess("SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM", {
              data: { id: 15 },
            })
          )
        );
      });
      it("should then stop form submission", () => {
        expect(generator.next({ form: "form_name" }).value).to.deep.equal(
          put(stopSubmit("form", {}))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callEditCustomerRelationshipTeam(
        writeCustomerRelationshipTeam({ id: 15, foo: "bar" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/sales/customer_relationship_teams/15", {
            id: 15,
            foo: "bar",
          })
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            "hydra:description": "ERREUR",
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed(
              "SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM",
              error.response
            )
          )
        );
      });
      it("should then generate errors", () => {
        expect(generator.next().value).to.deep.equal(
          call(generateFormErrors, error)
        );
      });
      it("should then autofill form", () => {
        expect(
          generator.next({ _error: "Internal Server Error." }).value
        ).to.deep.equal(put(autofill("form", "errorMessage", "ERREUR")));
      });
      it("should then stop form submission", () => {
        expect(
          generator.next({ _error: "Internal Server Error." }).value
        ).to.deep.equal(
          put(stopSubmit("form", { _error: "Internal Server Error." }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
