import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { fetchAPI } from "../../../utils/api";
import callGenericGetGenerator from "../../../sagas/common/generator";

describe("Generic sagas", () => {
  describe("callGenericGetGenerator", () => {
    describe("Successful calls", () => {
      const generator = callGenericGetGenerator({
        type: "TEST",
        payload: { request: { url: "/foo?bar" } },
      });
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(fetchAPI, "/foo?bar")
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: "test" }).value).to.deep.equal(
          put({
            type: "TEST_SUCCESS",
            payload: { data: "test" },
          })
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callGenericGetGenerator({
        type: "TEST",
        payload: { request: { url: "/foo?bar" } },
      });

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(fetchAPI, "/foo?bar")
        );
      });
      const error = { response: "test" };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put({
            type: "TEST_FAILED",
            payload: "test",
          })
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
