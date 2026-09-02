import { describe, it } from "mocha";
import { expect } from "chai";
import { getBusinessUnitsMapping } from "../../../selectors/businessUnit/businessUnitSelector";

const initialState: any = {
  businessUnit: {
    businessUnits: [],
  },
};

describe("businessUnitSelector", () => {
  describe("getBusinessUnitsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        businessUnit: {
          businessUnits: {
            "/foo/1": { name: "Foo", "@id": "/foo/1" },
            "/bar/2": { name: "Bar", "@id": "/bar/2" },
          },
        },
      };
      expect(getBusinessUnitsMapping(initialState)).to.deep.equal([]);
      expect(getBusinessUnitsMapping.recomputations()).to.equal(1);
      expect(
        getBusinessUnitsMapping(state).map((businessUnit) => {
          return { ...businessUnit };
        })
      ).to.deep.equal([
        { label: "Foo", value: "/foo/1" },
        { label: "Bar", value: "/bar/2" },
      ]);
      expect(getBusinessUnitsMapping.recomputations()).to.equal(2);
      getBusinessUnitsMapping(state);
      expect(getBusinessUnitsMapping.recomputations()).to.equal(2);
    });
  });
});
