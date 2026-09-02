import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import { client } from "../../../store";
import { callGetLogs } from "../../../sagas/activity/logSagas";
import { getLogs } from "../../../actions/activity/logsActions";

describe("logsSagas", () => {
  describe("callGetLogs", () => {
    describe("Successful calls", () => {
      const generator = callGetLogs(getLogs("/resource/2"));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.get, "/logs?resource=/resource/2&pagination=false")
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ iri: "/resource/2" }).value).to.deep.equal(
          put(APICallSuccess("ACTIVITY_FETCH_LOGS", { iri: "/resource/2" }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callGetLogs(getLogs("/resource/2"));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.get, "/logs?resource=/resource/2&pagination=false")
        );
      });
      const error = { response: { data: "test" } };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("ACTIVITY_FETCH_LOGS", { data: "test" }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
