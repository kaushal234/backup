import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/mis/typesActions";

describe("typesAction", () => {
  describe("fetchTypes", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "MIS_FETCH_TYPES",
        payload: {
          request: {
            url: "/mis/types?type=test&order[displayedOrder]=ASC",
          },
        },
      };
      expect(actions.fetchTypes("test")).to.deep.equal(expectedAction);
    });
  });
  describe("fetchType", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "MIS_FETCH_TYPE",
        payload: {
          form: "trouble_ticket_form",
          request: {
            url: "/mis/types/1",
          },
        },
      };
      expect(actions.fetchType(1)).to.deep.equal(expectedAction);
    });
  });
});
