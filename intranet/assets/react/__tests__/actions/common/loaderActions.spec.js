import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/common/loaderActions";

describe("loaderActions", () => {
  describe("setGlobalLoader", () => {
    it("should enable global loader action", () => {
      const expectedAction = {
        type: "COMMON_SET_LOADING",
        payload: {
          isLoading: true,
        },
      };
      expect(actions.setGlobalLoader(true)).to.deep.equal(expectedAction);
    });
  });
  describe("resetPrinters", () => {
    it("should disable global loader action", () => {
      const expectedAction = {
        type: "COMMON_SET_LOADING",
        payload: {
          isLoading: false,
        },
      };
      expect(actions.setGlobalLoader(false)).to.deep.equal(expectedAction);
    });
  });
});
