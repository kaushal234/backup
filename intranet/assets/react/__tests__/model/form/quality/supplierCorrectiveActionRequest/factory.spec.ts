import { describe, it } from "mocha";
import { expect } from "chai";
import { supplierCorrectiveActionRequestFactory } from "../../../../../model/form/quality/supplierCorrectiveActionRequest/factory";

const dummySupplierCorrectiveActionRequest = {
  id: 1,
  factory: {
    erp: 1,
    value: "/iri/1",
    label: "factory name",
  },
  importanceFactor: "IF1",
  shortDescription: "short description",
  description: "description",
  representative: {
    value: "/iri/1",
    label: "name",
  },
  leader: {
    value: "/iri/2",
    label: "name",
  },
  supplierNumber: {
    value: "/iri/3",
    name: "name",
    label: "label",
  },
  issueOrigin: "blah",
  correctiveAction: "blah",
  preventiveAction: "blah",
  commercialAgreement: "blah",
  verificationDescription: "blah",
  conclusion: "blah",
};

describe("SupplierCorrectiveActionRequestFactory", () => {
  it("should return a supplier corrective action request", () => {
    // this is a hack to avoid momentJS to generate distinct date and mark the test as failing sometimes
    const results = supplierCorrectiveActionRequestFactory(
      dummySupplierCorrectiveActionRequest
    );
    expect(results).to.deep.equal({
      id: 1,
      factory: "/iri/1",
      iFactor: "IF1",
      shortDescription: "short description",
      description: "description",
      representative: "/iri/1",
      leader: "/iri/2",
      supplierNumber: "/iri/3",
      issueOrigin: "blah",
      correctiveAction: "blah",
      preventiveAction: "blah",
      commercialAgreement: "blah",
      verificationDescription: "blah",
      conclusion: "blah",
    });
  });
});
