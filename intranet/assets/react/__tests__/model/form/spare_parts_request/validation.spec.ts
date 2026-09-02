import { describe, it } from "mocha";
import { expect } from "chai";
import Translator from "bazinga-translator";
import validate from "../../../../model/form/spare_parts_request/validation";

describe("spare_parts_request validation", () => {
  const defaultErrors = {
    _error: Translator.trans("spare_parts_request.errors.part"),
  };
  const defaultPartsErrors = {
    quantity: Translator.trans("shipping_quotation_request.errors.quantity"),
    unitOfMeasure: Translator.trans(
      "spare_parts_request.errors.unit_of_measure"
    ),
    partNumber: Translator.trans("spare_parts_request.errors.part_number"),
  };
  it("Should have default errors", () => {
    expect(validate({})).to.deep.equal(defaultErrors);
  });
  it("Should default error when a parts is empty", () => {
    expect(validate({ parts: [{}] })).to.deep.equal({
      _error: Translator.trans("spare_parts_request.errors.delivery_address"),
      parts: [{ ...defaultPartsErrors }],
    });
  });
  it("Should throw validation error when a new address is empty", () => {
    expect(
      validate({
        parts: [{ partNumber: "123456", unitOfMeasure: "EA", quantity: 1 }],
        newAddress: "true",
      })
    ).to.deep.equal({
      lastname: Translator.trans("spare_parts_request.errors.lastname"),
      firstname: Translator.trans("spare_parts_request.errors.firstname"),
      street1: Translator.trans("spare_parts_request.errors.street1"),
      phone: Translator.trans("spare_parts_request.errors.telephone"),
      postalCode: Translator.trans("spare_parts_request.errors.postal_code"),
      city: Translator.trans("spare_parts_request.errors.city"),
      country: Translator.trans("spare_parts_request.errors.country"),
      company: Translator.trans("spare_parts_request.errors.company"),
    });
  });
});
