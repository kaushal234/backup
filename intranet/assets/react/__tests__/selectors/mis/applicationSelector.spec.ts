import { describe, it } from "mocha";
import { expect } from "chai";
import { getApplicationsMapping } from "../../../selectors/mis/applicationSelector";

const initialState: any = {
  mis: {
    applications: [],
  },
};

describe("applicationSelect", () => {
  describe("getApplicationsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        mis: {
          applications: {
            "/foo/1": { "@id": "/foo/1", id: 1, name: "foo" },
            "/bar/2": { "@id": "/bar/2", id: 2, name: "bar" },
          },
        },
      };
      expect(getApplicationsMapping(initialState)).to.deep.equal([]);
      expect(getApplicationsMapping.recomputations()).to.equal(1);
      expect(getApplicationsMapping(state)).to.deep.equal([
        { value: "/foo/1", label: "foo" },
        { value: "/bar/2", label: "bar" },
      ]);
      expect(getApplicationsMapping.recomputations()).to.equal(2);
      getApplicationsMapping(state);
      expect(getApplicationsMapping.recomputations()).to.equal(2);
    });
  });
});
