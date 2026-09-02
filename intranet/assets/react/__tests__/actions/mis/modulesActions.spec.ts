import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/mis/modulesActions";

describe("modulesAction", () => {
  describe("fetchModules", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "MIS_FETCH_MODULES",
        payload: {
          request: {
            url: "/modules?application=test&order[name]=ASC&status=ACTIVE&disabledForTroubleTicket=false",
          },
        },
      };
      expect(actions.fetchModulesForTroubleTickets("test")).to.deep.equal(
        expectedAction
      );
    });
  });
});

describe("modulesAction", () => {
  describe("fetchModules", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "MIS_FETCH_MODULES",
        payload: {
          request: {
            url: "/modules",
          },
        },
      };
      expect(actions.fetchModules()).to.deep.equal(expectedAction);
    });
  });
});
