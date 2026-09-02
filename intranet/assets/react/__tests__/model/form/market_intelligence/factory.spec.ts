import { describe, it } from "mocha";
import { expect } from "chai";
import { marketIntelligenceFactory } from "../../../../model/form/market_intelligence/factory";

const dummyMIM = {
  id: null,
  competitors: [
    {
      label: "foo",
      value: "/foo/2",
    },
  ],
  customers: [],
  productTypes: [],
  suppliers: [{ value: "foo", name: "bar" }],
  divisions: { foo: true, bar: false },
  description: "Just for test",
  positionLevels: "foo bar",
  shortDescription: "Juste un bridou",
  type: {
    label: "TEST",
    value: "/bar/5",
  },
  url: "https://www.thisisatest.com",
  bar: "foo",
};
describe("MIM factory", () => {
  it("should return a Market Intelligence", () => {
    expect({ ...marketIntelligenceFactory(dummyMIM) }).to.deep.equal({
      id: null,
      competitors: ["/foo/2"],
      customers: [],
      productTypes: [],
      suppliers: ["bar"],
      divisions: ["foo", "/divisions/5"],
      positionLevels: ["foo", "bar"],
      description: "Just for test",
      shortDescription: "Juste un bridou",
      type: "/bar/5",
      url: "https://www.thisisatest.com",
    });
  });
});
