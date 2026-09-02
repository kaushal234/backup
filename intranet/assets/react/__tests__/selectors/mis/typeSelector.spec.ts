import { describe, it } from "mocha";
import { expect } from "chai";
import { getTypesMapping } from "../../../selectors/mis/typeSelector";

const initialState: any = {
  mis: {
    modules: [],
  },
};

describe("typeSelect", () => {
  describe("getTypesMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        mis: {
          types: {
            "/foo/1": { "@id": "/foo/1", id: 1, description: "foo" },
            "/bar/2": { "@id": "/bar/2", id: 2, description: "bar" },
          },
        },
      };
      expect(getTypesMapping(initialState)).to.deep.equal([]);
      expect(getTypesMapping.recomputations()).to.equal(1);
      expect(getTypesMapping(state)).to.deep.equal([
        {
          value: "/foo/1",
          label: "trouble_ticket.form.option_value.foo",
          id: 1,
        },
        {
          value: "/bar/2",
          label: "trouble_ticket.form.option_value.bar",
          id: 2,
        },
      ]);
      expect(getTypesMapping.recomputations()).to.equal(2);
      getTypesMapping(state);
      expect(getTypesMapping.recomputations()).to.equal(2);
    });
  });
});
