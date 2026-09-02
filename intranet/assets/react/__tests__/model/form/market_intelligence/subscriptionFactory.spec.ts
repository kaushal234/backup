import { describe, it } from "mocha";
import { expect } from "chai";
import { marketIntelligenceSubscriptionFactory } from "../../../../model/form/market_intelligence/subscriptionFactory";

const dummyMIMSubscription = {
  competitor: {
    label: "foo",
    value: "/foo/2",
  },
  type: {
    label: "bar",
    value: "/foo/4",
  },
};
const emptyMIMSubscription = {
  competitor: {
    label: "foo",
    value: "/foo/2",
  },
  type: {
    label: "bar",
    value: "/foo/4",
  },
  all: true,
};
describe("MIM factory", () => {
  it("should return a Market Intelligence Subscription", () => {
    expect({
      ...marketIntelligenceSubscriptionFactory(dummyMIMSubscription),
    }).to.deep.equal({
      competitor: "/foo/2",
      customer: null,
      productType: null,
      supplier: null,
      type: "/foo/4",
    });
  });
  it("should return an empty Market Intelligence Subscription", () => {
    expect({
      ...marketIntelligenceSubscriptionFactory(emptyMIMSubscription),
    }).to.deep.equal({});
  });
});
