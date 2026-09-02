import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/quality/SupplierCorrectiveActionRequestAction";
import {
  CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
  UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
} from "../../../constants";

describe("supplierCorrectiveActionRequestAction", () => {
  describe("When I want to create a supplier corrective action request", () => {
    it("should send the send the right payload to the saga", () => {
      const expectedDataAction = {
        type: CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
        payload: {
          url: "/quality/supplier_corrective_action_requests",
          body: {
            factory: "/iri/1",
            iFactor: "IF1",
            shortDescription: "shortDescription",
            description: "description",
            representative: "/iri/1",
            leader: null,
            supplierNumber: "/iri/1",
          },
        },
      };
      const actualDataAction = actions.createSupplierCorrectiveActionRequest({
        factory: "/iri/1",
        iFactor: "IF1",
        shortDescription: "shortDescription",
        description: "description",
        representative: "/iri/1",
        leader: null,
        supplierNumber: "/iri/1",
      });
      expect(actualDataAction).to.deep.equal(expectedDataAction);
    });
  });
  describe("When I want to update a supplier corrective action request", () => {
    it("should send the send the right payload to the saga", () => {
      const id = 1;
      const expectedDataAction = {
        type: UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
        payload: {
          url: `/quality/supplier_corrective_action_requests/${id}`,
          body: {
            id: 1,
            factory: "/iri/1",
            iFactor: "IF1",
            shortDescription: "shortDescription",
            description: "description",
            representative: "/iri/1",
            leader: null,
            supplierNumber: "/iri/1",
            issueOrigin: "blah",
            correctiveAction: "blah",
            preventiveAction: "blah",
            commercialAgreement: "blah",
            verificationDescription: "blah",
            conclusion: "blah",
          },
        },
      };
      const actualDataAction = actions.updateSupplierCorrectiveActionRequest({
        id: 1,
        factory: "/iri/1",
        iFactor: "IF1",
        shortDescription: "shortDescription",
        description: "description",
        representative: "/iri/1",
        leader: null,
        supplierNumber: "/iri/1",
        issueOrigin: "blah",
        correctiveAction: "blah",
        preventiveAction: "blah",
        commercialAgreement: "blah",
        verificationDescription: "blah",
        conclusion: "blah",
      });
      expect(actualDataAction).to.deep.equal(expectedDataAction);
    });
  });
});
