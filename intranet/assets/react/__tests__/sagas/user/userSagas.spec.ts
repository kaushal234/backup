import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { fetchAPI } from "../../../utils/api";
import { fetchConnectedUser } from "../../../actions/user/userActions";
import { callFetchConnectedUser } from "../../../sagas/user/userSagas";

describe("usersSagas", () => {
  describe("callFetchConnectedUser", () => {
    describe("Successful calls", () => {
      const generator = callFetchConnectedUser(fetchConnectedUser());
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(call(fetchAPI, "/me"));
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: "test" }).value).to.deep.equal(
          put({
            type: "USER_FETCH_CONNECTED_USER_SUCCESS",
            payload: { data: "test" },
          })
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callFetchConnectedUser(fetchConnectedUser());

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(call(fetchAPI, "/me"));
      });
      const error = { response: "test" };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put({
            type: "USER_FETCH_CONNECTED_USER_FAILED",
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
