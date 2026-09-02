import { describe, it } from "mocha";
import { expect } from "chai";
import { competitorPricingFactory } from "../../../../model/form/competitor_pricing/factory";

const dummyCPR = {
  competitor: "/foo/1",
  quotationDate: "2099-12-05",
  model: "TOTO",
  incoterms: "BOF",
  quantity: 8,
  price: 69,
  currency: "USD",
  bar: "foo",
};
describe("Competitor Pricing factory", () => {
  it("should return a Competitor Pricing", () => {
    expect({
      ...competitorPricingFactory(dummyCPR),
      quotationDate: null,
    }).to.deep.equal({
      competitor: "/foo/1",
      quotationDate: null,
      model: "TOTO",
      incoterms: "BOF",
      quantity: 8,
      price: 69,
      currency: "USD",
    });
  });
});
