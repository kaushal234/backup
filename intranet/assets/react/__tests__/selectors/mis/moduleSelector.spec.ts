import { describe, it } from "mocha";
import { expect } from "chai";
import { getModulesMapping } from "../../../selectors/mis/moduleSelector";

const initialState: any = {
  mis: {
    types: [],
  },
};

describe("typeSelect", () => {
  describe("getModulesMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        mis: {
          modules: {
            "/foo/1": {
              "@id": "/foo/1",
              id: 1,
              name: "foo",
              shortDescription: "foo foo",
              application: { name: "app1", "@id": "application/1" },
            },
            "/bar/2": {
              "@id": "/bar/2",
              id: 2,
              name: "bar",
              shortDescription: "bar bar",
              application: { name: "app2", "@id": "application/2" },
            },
          },
        },
      };
      expect(getModulesMapping(initialState)).to.deep.equal([]);
      expect(getModulesMapping.recomputations()).to.equal(1);
      expect(getModulesMapping(state)).to.deep.equal([
        {
          value: "/foo/1",
          label: "foo: foo foo - app1",
          application: { name: "app1", "@id": "application/1" },
        },
        {
          value: "/bar/2",
          label: "bar: bar bar - app2",
          application: { name: "app2", "@id": "application/2" },
        },
      ]);
      expect(getModulesMapping.recomputations()).to.equal(2);
      getModulesMapping(state);
      expect(getModulesMapping.recomputations()).to.equal(2);
    });
  });
});
