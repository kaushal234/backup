import { describe, it } from "mocha";
import { expect } from "chai";
import { getFinanceFamiliesMapping } from "../../../selectors/finance/financeFamilySelector";

const initialState: any = {
  finance: {
    financeFamilies: [],
  },
};

describe("financeFamilySelector", () => {
  describe("getFinanceFamiliesMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        finance: {
          financeFamilies: [
            {
              "@id": "/foo/1",
              name: "Foo",
              label: "Foo",
              value: "/foo/1",
              pricings: [],
            },
            { "@id": "/foo/2", name: "Bar", label: "Bar", value: "/foo/2" },
          ],
        },
      };
      expect(getFinanceFamiliesMapping(initialState)).to.deep.equal([]);
      expect(getFinanceFamiliesMapping.recomputations()).to.equal(1);
      expect(getFinanceFamiliesMapping(state)).to.deep.equal([
        {
          "@id": "/foo/1",
          name: "Foo",
          label: "Foo",
          value: "/foo/1",
          pricings: {},
        },
        { "@id": "/foo/2", name: "Bar", label: "Bar", value: "/foo/2" },
      ]);
      expect(getFinanceFamiliesMapping.recomputations()).to.equal(2);
      getFinanceFamiliesMapping(state);
      expect(getFinanceFamiliesMapping.recomputations()).to.equal(2);
    });
  });
});
