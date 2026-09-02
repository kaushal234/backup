import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../../actions/humanResources/event/eventActions";

describe("eventActions", () => {
  describe("addEvent", () => {
    it("should create a create action", () => {
      const expectedAction = {
        type: "EVENT_ADD_EVENT",
        payload: {
          request: {
            url: "/events",
            body: { foo: "bar" },
          },
        },
      };
      expect(actions.addEvent({ foo: "bar" })).to.deep.equal(expectedAction);
    });
  });
});
