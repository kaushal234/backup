import { describe, it } from "mocha";
import { expect } from "chai";
import { generateFormErrors } from "../../utils/api";

describe("API utils", () => {
  describe("generateFormErrors", () => {
    it("should return an error message if the error does not contain a response", () => {
      expect(generateFormErrors(new Error())).to.deep.equal({
        _error: "Error",
      });
      expect(generateFormErrors(new Error("Timeout exceeded"))).to.deep.equal({
        _error: "Error: Timeout exceeded",
      });
    });
    it("should return the error message when a message a present in the response", () => {
      const error = {
        response: { status: 404, data: { message: "Not Found." } },
      };
      expect(generateFormErrors(error)).to.deep.equal({ _error: "Not Found." });
    });
    it("should return a generic error when response data is malformed", () => {
      const error = { response: { status: 404, data: {} } };
      expect(generateFormErrors(error)).to.deep.equal({
        _error: "Internal Server Error.",
      });
    });
    it("should return the hydra:description of the error when not a validation error", () => {
      const error = {
        response: { status: 404, data: { "hydra:description": "Not Found." } },
      };
      expect(generateFormErrors(error)).to.deep.equal({ _error: "Not Found." });
    });
    it("should return the hydra:description of the error when not a validation error", () => {
      const error = {
        response: {
          status: 400,
          data: {
            violations: [
              {
                propertyPath: "email",
                message: "not a valid email.",
              },
              {
                propertyPath: "lechien",
                message: "path le chien !",
              },
              {
                propertyPath: "nested[2].prop",
                message: "how deep is your prop",
              },
            ],
          },
        },
      };
      expect(generateFormErrors(error)).to.deep.equal({
        email: "not a valid email.",
        lechien: "path le chien !",
        nested: [undefined, undefined, { prop: "how deep is your prop" }],
      });
    });
  });
});
