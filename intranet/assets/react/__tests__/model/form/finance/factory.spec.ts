import { describe, it } from "mocha";
import { expect } from "chai";
import { financeFamilyFactory } from "../../../../model/form/finance/factory";

const dummyFinanceFamily = {
  name: "Test",
  pricings: {
    "/sso/1-/factory/1": {
      "@id": "/foo/1",
      averagePrice: 9,
      averageMargin: 10,
    },
    "/sso/1-/factory/3": {
      "@id": "/foo/2",
      averageMargin: 10,
    },
    "/sso/1-/factory/2": {
      averagePrice: 9,
      averageMargin: 11,
    },
  },
  factories: ["/factory/1"],
  bar: "bar",
};
describe("Finance Family update factory", () => {
  it("should return a Product", () => {
    expect({
      ...financeFamilyFactory({ ...dummyFinanceFamily, id: 21 }),
    }).to.deep.equal({
      id: 21,
      name: "Test",
      factories: ["/factory/1"],
      pricings: [
        {
          "@id": "/foo/1",
          averagePrice: 9,
          averageMargin: 10,
          sso: "/sso/1",
          factory: "/factory/1",
        },
        {
          averagePrice: 9,
          averageMargin: 11,
          sso: "/sso/1",
          factory: "/factory/2",
        },
      ],
    });
  });
});

describe("Finance Family create factory", () => {
  it("should return a Product", () => {
    expect({ ...financeFamilyFactory(dummyFinanceFamily) }).to.deep.equal({
      name: "Test",
      factories: ["/factory/1"],
      pricings: [
        {
          averagePrice: 9,
          averageMargin: 10,
          sso: "/sso/1",
          factory: "/factory/1",
        },
        {
          averagePrice: 9,
          averageMargin: 11,
          sso: "/sso/1",
          factory: "/factory/2",
        },
      ],
    });
  });
});
