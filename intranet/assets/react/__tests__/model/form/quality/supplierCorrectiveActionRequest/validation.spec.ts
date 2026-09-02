import { describe, it } from "mocha";
import { expect } from "chai";
import Translator from "bazinga-translator";
import validate from "../../../../../model/form/quality/supplierCorrectiveActionRequest/validation";
import { ISupplierCorrectiveActionRequestFactoryProps } from "../../../../../types/ISupplierCorrectiveActionRequestFactoryProps";

describe("supplier corrective action request validation", () => {
  const defaultErrors = {
    factory: Translator.trans(
      "supplier_corrective_action_request.errors.factory"
    ),
    importanceFactor: Translator.trans(
      "supplier_corrective_action_request.errors.importance_factor"
    ),
    shortDescription: Translator.trans(
      "supplier_corrective_action_request.errors.short_description"
    ),
    description: Translator.trans(
      "supplier_corrective_action_request.errors.description"
    ),
    representative: Translator.trans(
      "supplier_corrective_action_request.errors.representative"
    ),
    supplierNumber: Translator.trans(
      "supplier_corrective_action_request.errors.supplier_number"
    ),
  };
  it("Should have default errors", () => {
    expect(
      validate({} as ISupplierCorrectiveActionRequestFactoryProps)
    ).to.deep.equal(defaultErrors);
  });
});
