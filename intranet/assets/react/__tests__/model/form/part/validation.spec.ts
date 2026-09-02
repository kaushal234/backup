import { describe, it } from "mocha";
import { expect } from "chai";
import Translator from "bazinga-translator";
import validate from "../../../../model/form/part/validation";

describe("part validation", () => {
  const defaultPartsErrors = {
    quantity: Translator.trans("shipping_quotation_request.errors.quantity"),
    unitOfMeasure: Translator.trans(
      "spare_parts_request.errors.unit_of_measure"
    ),
    partNumber: Translator.trans("spare_parts_request.errors.part_number"),
  };
  it("Should default error when a parts is empty except for NCR", () => {
    expect(validate({ parts: [{ module: "NCR" }] })).to.deep.equal({
      parts: [
        {
          unitOfMeasure: Translator.trans(
            "spare_parts_request.errors.unit_of_measure"
          ),
          partNumber: Translator.trans(
            "spare_parts_request.errors.part_number"
          ),
        },
      ],
    });
  });
  it("Should default error when a parts is empty except for VWC", () => {
    expect(validate({ parts: [{ module: "VWC" }] })).to.deep.equal({
      parts: [
        {
          unitOfMeasure: Translator.trans(
            "spare_parts_request.errors.unit_of_measure"
          ),
          partNumber: Translator.trans(
            "spare_parts_request.errors.part_number"
          ),
        },
      ],
    });
  });
  it("Should default error when a parts is empty", () => {
    expect(validate({ parts: [{}] })).to.deep.equal({
      parts: [{ ...defaultPartsErrors }],
    });
  });
});
