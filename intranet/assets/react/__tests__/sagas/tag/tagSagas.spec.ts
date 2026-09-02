import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put, takeEvery } from "redux-saga/effects";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import { client } from "../../../store";
import { TAG_FETCH_LIST } from "../../../constants";
import watchTagsSagas, { callGetTags } from "../../../sagas/tag/tagSagas";

describe("watchTagsSagas", () => {
  describe("fetchTags", () => {
    describe("Successful API call", () => {
      const action = { type: TAG_FETCH_LIST, payload: { url: "/tags" } };
      const generator = callGetTags(action);

      it("should call the API", () => {
        expect(generator.next().value).to.deep.equal(call(client.get, "/tags"));
      });

      it("should dispatch a success action on successful API call", () => {
        const response = {
          data: { "hydra:member": [{ "@id": "/tags/1", name: "Tag 1" }] },
        };
        expect(generator.next(response).value).to.deep.equal(
          put(APICallSuccess(TAG_FETCH_LIST, response))
        );
      });

      it("should be done after dispatching success action", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failed API call", () => {
      const action = { type: TAG_FETCH_LIST, payload: { url: "/tags" } };
      const generator = callGetTags(action);

      it("should call the API", () => {
        expect(generator.next().value).to.deep.equal(call(client.get, "/tags"));
      });

      it("should dispatch a failed action on failed API call", () => {
        const error = {
          response: { status: 404, data: { error: "Not Found" } },
        };
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed(TAG_FETCH_LIST, error.response))
        );
      });

      it("should be done after dispatching failed action", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });

  describe("watchTagsSagas", () => {
    const saga = watchTagsSagas();

    it("should take every TAG_FETCH_LIST action", () => {
      expect(saga.next().value).to.deep.equal(
        takeEvery(TAG_FETCH_LIST, callGetTags)
      );
    });

    it("should be done after setting up the watcher", () => {
      expect(saga.next().done).to.be.true;
    });
  });
});
