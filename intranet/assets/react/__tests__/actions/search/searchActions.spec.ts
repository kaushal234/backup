import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/search/searchActions";

describe("searchActions", () => {
  describe("setSearchText", () => {
    it("should create an action to set search text", () => {
      const expectedAction = {
        type: "SEARCH_SET_FILTER_TEXT",
        payload: {
          filterText: "test",
        },
      };
      expect(actions.setSearchText("test")).to.deep.equal(expectedAction);
    });
  });
});
