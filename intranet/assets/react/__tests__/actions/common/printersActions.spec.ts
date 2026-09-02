import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/common/printersActions";

describe("printersActions", () => {
  describe("fetchPrinters", () => {
    it("should create a fetch printers action", () => {
      const expectedAction = {
        type: "COMMON_FETCH_PRINTERS",
        payload: {
          request: {
            url: "/printers?type=null",
          },
        },
      };
      expect(actions.fetchPrinters()).to.deep.equal(expectedAction);
      expectedAction.payload.request.url = "/printers?type=LABELPRINTER";
      expect(actions.fetchPrinters("LABELPRINTER")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("resetPrinters", () => {
    it("should create a fetch printers action", () => {
      expect(actions.resetPrinters()).to.deep.equal({
        type: "COMMON_FETCH_PRINTERS_RESET",
      });
    });
  });
});
