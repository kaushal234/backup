import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/nonConformity/nonConformityActions";

describe("nonConformityAction", () => {
  describe("fetchAsyncUNonformitiess", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "NON_CONFORMITY_FETCH_NON_CONFORMITIES",
        payload: {
          request: {
            url: "/quality/non_conformities?id=2",
          },
        },
      };
      expect(actions.fetchNonConformitiesList("2")).to.deep.equal(
        expectedAction
      );
    });
  });
});
