import { describe, it } from "mocha";
import { expect } from "chai";
import productFactory from "../../../../model/form/catalogue/factory";

const dummyProduct = {
  id: 21,
  financeFamily: "finance/finance_families/1",
  bar: "bar",
};
describe("Product factory", () => {
  it("should return a Product", () => {
    expect({ ...productFactory(dummyProduct) }).to.deep.equal({
      id: 21,
      financeFamily: "finance/finance_families/1",
    });
  });
});
