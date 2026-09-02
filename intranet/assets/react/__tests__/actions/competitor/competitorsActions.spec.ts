import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/competitors/competitorsActions";

describe("competitorsActions", () => {
  describe("fetchCompetitor", () => {
    it("should create a fetch competitor action", () => {
      const expectedAction = {
        type: "COMPETITORS_FETCH_COMPETITOR",
        payload: {
          request: {
            url: "/resource/1",
          },
        },
      };
      expect(actions.fetchCompetitor("/resource/1")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("fetchCompetitors", () => {
    it("should create a fetch competitors action", () => {
      const expectedAction = {
        type: "COMPETITORS_FETCH_COMPETITORS",
        payload: {
          request: {
            url: `/sales/competitors?normalizationGroupsOverride[]=competitor_list&order[name]=asc&q=AIR`,
          },
        },
      };
      expect(actions.fetchCompetitors("AIR")).to.deep.equal(expectedAction);
    });
  });
});
