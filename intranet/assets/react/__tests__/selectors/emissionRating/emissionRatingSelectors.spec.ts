import { describe, it } from "mocha";
import { expect } from "chai";
import { getEmissionRatingsMapping } from "../../../selectors/emissionRating/emissionRatingSelectors";

const initialState: any = {
  emissionRating: {
    emissionRatings: [],
  },
};

describe("emissionRatingSelector", () => {
  describe("getEmissionRatingsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        emissionRating: {
          emissionRatings: {
            "/foo/1": { name: "Foo", "@id": "/foo/1" },
            "/bar/2": { name: "Bar", "@id": "/bar/2" },
          },
        },
      };
      expect(getEmissionRatingsMapping(initialState)).to.deep.equal([]);
      expect(getEmissionRatingsMapping.recomputations()).to.equal(1);
      expect(
        getEmissionRatingsMapping(state).map((tier) => {
          return { ...tier };
        })
      ).to.deep.equal([
        { label: "Foo", value: "/foo/1" },
        { label: "Bar", value: "/bar/2" },
      ]);
      expect(getEmissionRatingsMapping.recomputations()).to.equal(2);
      getEmissionRatingsMapping(state);
      expect(getEmissionRatingsMapping.recomputations()).to.equal(2);
    });
  });
});
