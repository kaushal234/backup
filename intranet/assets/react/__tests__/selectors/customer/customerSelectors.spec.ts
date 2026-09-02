import { describe, it } from "mocha";
import { expect } from "chai";
import { getSalesCustomersSelectMapping } from "../../../selectors/customer/customersSelector";

const initialState: any = {
  customer: {
    customers: [],
  },
};

describe("customerSelector", () => {
  describe("getSalesCustomersSelectMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        customer: {
          customers: {
            "/foo/1": { name: "Foo", "@id": "/foo/1", status: "APPROVED" },
            "/bar/2": { name: "Bar", "@id": "/bar/2", status: "NOT APPROVED" },
          },
        },
      };
      expect(getSalesCustomersSelectMapping(initialState)).to.deep.equal([]);
      expect(getSalesCustomersSelectMapping.recomputations()).to.equal(1);
      expect(
        getSalesCustomersSelectMapping(state).map((customer) => {
          return { ...customer };
        })
      ).to.deep.equal([
        { label: "Foo", value: "/foo/1" },
        { label: "Bar (status: NOT APPROVED)", value: "/bar/2" },
      ]);
      expect(getSalesCustomersSelectMapping.recomputations()).to.equal(2);
      getSalesCustomersSelectMapping(state);
      expect(getSalesCustomersSelectMapping.recomputations()).to.equal(2);
    });
  });
});
