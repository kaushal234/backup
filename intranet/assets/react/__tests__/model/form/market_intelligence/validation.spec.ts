import { describe, it } from "mocha";
import { expect } from "chai";
import validate from "../../../../model/form/market_intelligence/validation";

describe("market intelligence validation", () => {
  const defaultErrors = {
    type: "Type is mandatory.",
    shortDescription: "Short description is mandatory.",
    description: "Description is mandatory.",
  };
  const defaultErrorWithCPR = {
    competitorPricing: {
      quotationDate: "Quotation date is mandatory for competitor pricing.",
      competitor: "Competitor is mandatory for competitor pricing.",
      price: "Price is mandatory for competitor pricing.",
      termsOfDelivery: "Terms of delivery is mandatory for competitor pricing.",
      quantity:
        "Quantity date is mandatory for competitor pricing and must be superior than 0.",
      model: "Model date is mandatory for competitor pricing.",
      currency: "Currency date is mandatory for competitor pricing.",
    },
  };
  it("Should have default errors", () => {
    expect(validate({})).to.deep.equal(defaultErrors);
  });
  it("Should throw validation error when description is too long", () => {
    expect(
      validate({
        shortDescription:
          "Postulabit honesta honesta valeat amicorum igitur valeat postulabit suadentium ut dare valeat dare lex ",
      })
    ).to.deep.equal({
      type: "Type is mandatory.",
      shortDescription:
        "Short description should be shorter than 75 characters.",
      description: "Description is mandatory.",
    });
  });
  it("Should throw validation error when missing one of customer, competitors or product types", () => {
    expect(validate({ submit: true, type: "test" })).to.deep.equal({
      shortDescription: "Short description is mandatory.",
      description: "Description is mandatory.",
      _error:
        "One of Customer, Competitor, Supplier or Product Type should not be null.",
    });
  });
  it("Should throw default validation error when competitor pricing is empty", () => {
    expect(validate({ competitorPricing: {} })).to.deep.equal(defaultErrors);
  });
  it("Should throw default CPR validation error when competitor pricing is wrong", () => {
    expect(validate({ competitorPricing: { quantity: -1 } })).to.deep.equal({
      ...defaultErrors,
      ...defaultErrorWithCPR,
    });
  });
});
