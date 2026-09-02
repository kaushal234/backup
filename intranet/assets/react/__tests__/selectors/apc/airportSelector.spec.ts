import { describe, it } from "mocha";
import { expect } from "chai";
import { getAirportsMapping } from "../../../selectors/apc/airportSelector";

const initialState: any = {
  apc: {
    airports: [],
  },
};

describe("airportSelector", () => {
  describe("getAirportsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        apc: {
          airports: {
            "/airports/1": {
              "@id": "/airports/1",
              code: "DTC",
              cityName: "Somewhere",
            },
            "/airports/2": {
              "@id": "/airports/2",
              code: "WTF",
              cityName: "Somewhere else",
            },
          },
        },
      };
      expect(getAirportsMapping(initialState)).to.deep.equal([]);
      expect(getAirportsMapping.recomputations()).to.equal(1);
      expect(getAirportsMapping(state)).to.deep.equal([
        { value: "/airports/1", label: "DTC - Somewhere" },
        { value: "/airports/2", label: "WTF - Somewhere else" },
      ]);
      expect(getAirportsMapping.recomputations()).to.equal(2);
      getAirportsMapping(state);
      expect(getAirportsMapping.recomputations()).to.equal(2);
    });
  });
});
