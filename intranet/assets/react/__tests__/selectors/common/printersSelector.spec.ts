import { describe, it } from "mocha";
import { expect } from "chai";
import { getPrintersListMapping } from "../../../selectors/common/printersSelectors";

const initialState: any = {
  printers: {
    printers: [],
  },
};

describe("printersSelector", () => {
  describe("getPrintersListMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        printers: {
          printers: [
            {
              "@id": "/printers/LABELPRINTER_500_510_540_SOR_ZEBRA_1",
              "@type": "Printer",
              reference: "LABELPRINTER_500_510_540_SOR_ZEBRA_1",
              name: "SOR_ZEBRA_1",
              type: "LABELPRINTER",
              erps: [500, 510, 540],
            },
            {
              "@id": "/printers/LABELPRINTER_MIS_SOR",
              "@type": "Printer",
              reference: "LABELPRINTER_MIS_SOR",
              name: "MIS_SOR",
              type: "LABELPRINTER",
              erps: [],
            },
          ],
        },
      };
      expect(getPrintersListMapping(initialState)).to.deep.equal([]);
      expect(getPrintersListMapping.recomputations()).to.equal(1);
      expect(getPrintersListMapping(state)).to.deep.equal([
        { value: "LABELPRINTER_500_510_540_SOR_ZEBRA_1", label: "SOR_ZEBRA_1" },
        { value: "LABELPRINTER_MIS_SOR", label: "MIS_SOR" },
      ]);
      expect(getPrintersListMapping.recomputations()).to.equal(2);
      getPrintersListMapping(state);
      expect(getPrintersListMapping.recomputations()).to.equal(2);
    });
  });
});
