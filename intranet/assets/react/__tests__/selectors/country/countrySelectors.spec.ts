import { describe, it } from "mocha";
import { expect } from "chai";
import { getCountriesMapping } from "../../../selectors/country/countrySelector";

const initialState: any = {
  country: {
    countries: [],
  },
};

describe("countrySelector", () => {
  describe("getCountriesMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        country: {
          countries: {
            "/foo/1": { name: "Foo", "@id": "/foo/1", isoCode2: "FR" },
            "/bar/2": { name: "Bar", "@id": "/bar/2", isoCode2: "US" },
          },
        },
      };
      expect(getCountriesMapping(initialState)).to.deep.equal([]);
      expect(getCountriesMapping.recomputations()).to.equal(1);
      expect(
        getCountriesMapping(state).map((country) => {
          return { ...country };
        })
      ).to.deep.equal([
        { label: "Foo", value: "/foo/1", isoCode2: "FR" },
        { label: "Bar", value: "/bar/2", isoCode2: "US" },
      ]);
      expect(getCountriesMapping.recomputations()).to.equal(2);
      getCountriesMapping(state);
      expect(getCountriesMapping.recomputations()).to.equal(2);
    });
  });
});
