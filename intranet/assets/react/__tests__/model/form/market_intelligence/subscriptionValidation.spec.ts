import { describe, it } from "mocha";
import { expect } from "chai";
import validate from "../../../../model/form/market_intelligence/subscriptionValidation";

describe("market intelligence subscription validation", () => {
  it("Should have default errors", () => {
    expect(validate({})).to.deep.equal({
      _error: "One field at least is mandatory.",
    });
  });
  it("Should be valid if all is checked", () => {
    expect(validate({ all: true })).to.deep.equal({});
  });
});
