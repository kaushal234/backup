import { describe, it } from "mocha";
import { expect } from "chai";
import { getDMSMapping } from "../../../selectors/dms/dmsSelector";

const initialState: any = {
  dms: {
    dms: [],
  },
};

describe("dmsSelector", () => {
  describe("getDMSMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        dms: {
          dms: {
            "/dms/1": { "@id": "/dms/1", title: "ETST", legacyId: 1524 },
            "/dms/2": { "@id": "/dms/2", title: "TEST", legacyId: 1254 },
          },
        },
      };
      expect(getDMSMapping(initialState)).to.deep.equal([]);
      expect(getDMSMapping.recomputations()).to.equal(1);
      expect(getDMSMapping(state)).to.deep.equal([
        { value: "/dms/1", label: "1524 - ETST" },
        { value: "/dms/2", label: "1254 - TEST" },
      ]);
      expect(getDMSMapping.recomputations()).to.equal(2);
      getDMSMapping(state);
      expect(getDMSMapping.recomputations()).to.equal(2);
    });
  });
});
