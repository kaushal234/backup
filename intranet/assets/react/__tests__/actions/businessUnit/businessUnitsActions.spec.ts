import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/businessUnit/businessUnitActions";

describe("businessUnitActions", () => {
  describe("fetchBusinessUnits", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "DIRECTORY_FETCH_BUSINESS_UNITS",
        payload: {
          request: {
            url: "/business_units?order[name]=asc",
          },
        },
      };
      expect(actions.fetchBusinessUnits()).to.deep.equal(expectedAction);
    });
  });
});
