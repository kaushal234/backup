import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/activity/logsActions";

describe("logsActions", () => {
  describe("getLogs", () => {
    it("should create a fetch logs action", () => {
      const expectedAction = {
        type: "ACTIVITY_FETCH_LOGS",
        payload: {
          url: "/logs?resource=/resources/42&pagination=false",
          iri: "/resources/42",
        },
      };
      expect(actions.getLogs("/resources/42")).to.deep.equal(expectedAction);
    });
  });
});
