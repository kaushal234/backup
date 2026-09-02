import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { fetchAPI } from "../../../utils/api";
import { fetchNonConformitiesList } from "../../../actions/nonConformity/nonConformityActions";
import { callFetchNonConformitiesList } from "../../../sagas/nonConformity/nonConformitySagas";

describe("nonConformitySagas", () => {
  describe("callFetchNonConformitiesList", () => {
    describe("Successful calls for TLD", () => {
      const generator = callFetchNonConformitiesList(
        fetchNonConformitiesList("2")
      );

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(fetchAPI, "/quality/non_conformities?id=2")
        );
      });
      it("should then dispatch a success event", () => {
        const data = {
          foo: "bar",
        };
        expect(generator.next({ data }).value).to.deep.equal(
          put({
            type: "NON_CONFORMITY_FETCH_NON_CONFORMITIES_SUCCESS",
            payload: {
              data,
            },
          })
        );
      });
    });

    describe("Failing calls", () => {
      const generator = callFetchNonConformitiesList(
        fetchNonConformitiesList(2)
      );
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(fetchAPI, "/quality/non_conformities?id=2")
        );
      });
      const error = { response: "test" };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put({
            type: "NON_CONFORMITY_FETCH_NON_CONFORMITIES_FAILED",
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
