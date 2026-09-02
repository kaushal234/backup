import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import { client } from "../../../store";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import { generateFormErrors } from "../../../utils/api";
import { callAdminParts } from "../../../sagas/part/partSagas";
import { writePartObject } from "../../../actions/part/partActions";

describe("partSagas", () => {
  describe("callAdminParts", () => {
    describe("Successful calls", () => {
      const generator = callAdminParts(
        writePartObject({ foo: "bar", "@id": "/foo/1" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/foo/1", { foo: "bar", "@id": "/foo/1" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({ data: { foo: "bar", "@id": "/foo/1" } }).value
        ).to.deep.equal(
          put(
            APICallSuccess("PART_EDIT_PART_OBJECT", {
              data: { foo: "bar", "@id": "/foo/1" },
            })
          )
        );
      });
      it("should then stop form submission", () => {
        expect(generator.next({ form: "form" }).value).to.deep.equal(
          put(stopSubmit("form", {}))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callAdminParts(
        writePartObject({ foo: "bar", "@id": "/foo/1" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/foo/1", { foo: "bar", "@id": "/foo/1" })
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
          put(APICallFailed("PART_EDIT_PART_OBJECT", error.response))
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
        expect(generator.next().value).to.deep.equal(
          put(stopSubmit("form", { _error: "Internal Server Error." }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
