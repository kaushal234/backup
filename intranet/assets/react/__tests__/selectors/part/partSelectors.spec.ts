import { describe, it } from "mocha";
import { expect } from "chai";
import { getPartsListMapping } from "../../../selectors/part/itemMonologisticSelectors";

const initialState: any = {
  part: {
    parts: [],
  },
};

describe("partSelectors", () => {
  describe("getPartsListMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        part: {
          parts: [
            { "@id": "/foo/1", itemCode: "123456", description: "description" },
          ],
        },
      };
      expect(getPartsListMapping(initialState)).to.deep.equal([]);
      expect(getPartsListMapping.recomputations()).to.equal(1);
      expect(getPartsListMapping(state)).to.deep.equal([
        {
          value: "123456",
          label: "123456 - description",
          "@id": "/foo/1",
          description: "description",
          itemCode: "123456",
        },
      ]);
      expect(getPartsListMapping.recomputations()).to.equal(2);
      getPartsListMapping(state);
      expect(getPartsListMapping.recomputations()).to.equal(2);
    });
  });
});
