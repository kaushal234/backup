import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import { client } from "../../../store";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import { generateFormErrors } from "../../../utils/api";
import { writeExtranetUserAcl } from "../../../actions/extranetUser/extranetUserAclActions";
import { callCreateExtranetUserAcl } from "../../../sagas/extranetUser/extranetUserAclSagas";

describe("extranetUserAclSagas", () => {
  describe("callCreateExtranetUserAcl", () => {
    describe("Successful calls", () => {
      const generator = callCreateExtranetUserAcl(
        writeExtranetUserAcl({ foo: "bar" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/sales/extranet_user_acls", { foo: "bar" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(
            APICallSuccess("EXTRANET_USER_CREATE_EXTRANET_USER_ACLS", {
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
      const generator = callCreateExtranetUserAcl(
        writeExtranetUserAcl({ foo: "bar" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/sales/extranet_user_acls", { foo: "bar" })
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
              "EXTRANET_USER_CREATE_EXTRANET_USER_ACLS",
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
