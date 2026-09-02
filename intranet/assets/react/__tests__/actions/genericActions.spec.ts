import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../actions/genericActions";

describe("genericActions", () => {
  describe("APICallSuccess", () => {
    it("should create a success action", () => {
      const expectedAction = {
        type: "RANDOM_TYPE_SUCCESS",
        payload: { foo: "bar" },
      };
      expect(
        actions.APICallSuccess("RANDOM_TYPE", { foo: "bar" })
      ).to.deep.equal(expectedAction);
    });
  });
  describe("APICallFailed", () => {
    it("should create a failed action", () => {
      const expectedAction = {
        type: "RANDOM_TYPE_FAILED",
        payload: { foo: "bar" },
      };
      expect(
        actions.APICallFailed("RANDOM_TYPE", { foo: "bar" })
      ).to.deep.equal(expectedAction);
    });
  });
  describe("hideErrorAlert", () => {
    it("should create an hide error alert action", () => {
      const expectedAction = {
        type: "FOO_HIDE_ERROR_ALERT",
      };
      expect(actions.hideErrorAlert("FOO")).to.deep.equal(expectedAction);
    });
  });
  describe("hideSuccessAlert", () => {
    it("should create an hide success alert action", () => {
      const expectedAction = {
        type: "FOO_HIDE_SUCCESS_ALERT",
      };
      expect(actions.hideSuccessAlert("FOO")).to.deep.equal(expectedAction);
    });
  });
  describe("redirect", () => {
    it("should create a redirect 404 by default", () => {
      const expectedAction = {
        type: "SPQ_REDIRECT_404",
      };
      expect(actions.redirect("SPQ")).to.deep.equal(expectedAction);
    });
    it("should create a custom redirect", () => {
      const expectedAction = {
        type: "FOO_REDIRECT_403",
      };
      expect(actions.redirect("FOO", 403)).to.deep.equal(expectedAction);
    });
  });
});
