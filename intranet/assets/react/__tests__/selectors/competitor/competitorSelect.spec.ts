import { describe, it } from "mocha";
import { expect } from "chai";
import { getCompetitorsMapping } from "../../../selectors/competitor/competitorSelect";

const initialState: any = {
  competitor: {
    competitors: [],
  },
};

describe("competitorSelect", () => {
  describe("getCompetitorsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        competitor: {
          competitors: {
            "/foo/1": { name: "Foo", "@id": "/foo/1" },
            "/bar/2": { name: "Bar", "@id": "/bar/2" },
          },
        },
      };
      expect(getCompetitorsMapping(initialState)).to.deep.equal([]);
      expect(getCompetitorsMapping.recomputations()).to.equal(1);
      expect(
        getCompetitorsMapping(state).map((competitor) => {
          return { ...competitor };
        })
      ).to.deep.equal([
        { label: "Foo", value: "/foo/1" },
        { label: "Bar", value: "/bar/2" },
      ]);
      expect(getCompetitorsMapping.recomputations()).to.equal(2);
      getCompetitorsMapping(state);
      expect(getCompetitorsMapping.recomputations()).to.equal(2);
    });
  });
});
