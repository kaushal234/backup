import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { client } from "../../../../store";
import {
  APICallSuccess,
  APICallFailed,
} from "../../../../actions/genericActions";
import { callCreateEvent } from "../../../../sagas/humanResources/event/eventSagas";
import { addEvent } from "../../../../actions/humanResources/event/eventActions";

describe("eventSagas", () => {
  describe("callCreateEvent", () => {
    describe("Successful calls", () => {
      const generator = callCreateEvent(addEvent({ foo: "bar" }));
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/events", { foo: "bar" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(APICallSuccess("EVENT_ADD_EVENT", { data: { id: 15 } }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callCreateEvent(addEvent({ foo: "bar" }));
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/events", { foo: "bar" })
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
          put(APICallFailed("EVENT_ADD_EVENT", error.response))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
