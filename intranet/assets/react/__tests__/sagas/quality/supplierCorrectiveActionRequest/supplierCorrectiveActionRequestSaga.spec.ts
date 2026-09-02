import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { client } from "../../../../store";
import {
  APICallSuccess,
  APICallFailed,
} from "../../../../actions/genericActions";
import {
  callCreateSupplierCorrectiveActionRequest,
  callUpdateSupplierCorrectiveActionRequest,
} from "../../../../sagas/quality/supplierCorrectiveActionRequest/SupplierCorrectiveActionRequestSaga";
import {
  createSupplierCorrectiveActionRequest,
  updateSupplierCorrectiveActionRequest,
} from "../../../../actions/quality/SupplierCorrectiveActionRequestAction";
import {
  CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
  UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
} from "../../../../constants";
import { ISupplierCorrectiveActionRequestActionResponse } from "../../../../types/ISupplierCorrectiveActionRequestActionResponse";

const mockResponse: ISupplierCorrectiveActionRequestActionResponse = {
  data: {
    id: 5,
    factory: "",
    iFactor: "IF1",
    shortDescription: "short desc",
    description: "desc",
    representative: "/iri/1",
    leader: "/iri/1",
    supplierNumber: "ADE100",
  },
  status: 200,
  statusText: "OK",
  headers: {
    "cache-control": "",
    "content-language": "",
    "content-type": "",
    link: "",
  },
};

describe("taskSagas", () => {
  describe("callCreateSupplierCorrectiveActionRequest", () => {
    describe("Successful calls", () => {
      const generator = callCreateSupplierCorrectiveActionRequest(
        createSupplierCorrectiveActionRequest({
          factory: "/iri/1",
          iFactor: "IF1",
          shortDescription: "shortDescription",
          description: "description",
          representative: "/iri/1",
          supplierNumber: "/iri/1",
          leader: null,
        })
      );
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/quality/supplier_corrective_action_requests", {
            factory: "/iri/1",
            iFactor: "IF1",
            shortDescription: "shortDescription",
            description: "description",
            representative: "/iri/1",
            supplierNumber: "/iri/1",
            leader: null,
          })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next(mockResponse).value).to.deep.equal(
          put(
            APICallSuccess(
              CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
              mockResponse
            )
          )
        );
      });
    });
    describe("Failing calls", () => {
      const generator = callCreateSupplierCorrectiveActionRequest(
        createSupplierCorrectiveActionRequest({
          factory: "/iri/1",
          iFactor: "IF1",
          shortDescription: "shortDescription",
          description: "description",
          representative: "/iri/1",
          supplierNumber: "/iri/1",
          leader: null,
        })
      );
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/quality/supplier_corrective_action_requests", {
            factory: "/iri/1",
            iFactor: "IF1",
            shortDescription: "shortDescription",
            description: "description",
            representative: "/iri/1",
            supplierNumber: "/iri/1",
            leader: null,
          })
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            violations: [
              {
                propertyPath: "pathLeChien",
                message: "in a bottle",
              },
            ],
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed(
              CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
              error.response
            )
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callUpdateSupplierCorrectiveActionRequest", () => {
    describe("Successful calls", () => {
      const generator = callUpdateSupplierCorrectiveActionRequest(
        updateSupplierCorrectiveActionRequest({
          id: 1,
          factory: "/iri/1",
          iFactor: "IF1",
          shortDescription: "shortDescription",
          description: "description",
          representative: "/iri/1",
          supplierNumber: "/iri/1",
          leader: null,
          issueOrigin: "blah",
          correctiveAction: "blah",
          preventiveAction: "blah",
          commercialAgreement: "blah",
          verificationDescription: "blah",
          conclusion: "blah",
        })
      );
      it("should then fetch the API", () => {
        const id = 1;
        expect(generator.next().value).to.deep.equal(
          call(
            client.put,
            `/quality/supplier_corrective_action_requests/${id}`,
            {
              id: 1,
              factory: "/iri/1",
              iFactor: "IF1",
              shortDescription: "shortDescription",
              description: "description",
              representative: "/iri/1",
              supplierNumber: "/iri/1",
              leader: null,
              issueOrigin: "blah",
              correctiveAction: "blah",
              preventiveAction: "blah",
              commercialAgreement: "blah",
              verificationDescription: "blah",
              conclusion: "blah",
            }
          )
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next(mockResponse).value).to.deep.equal(
          put(
            APICallSuccess(
              UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
              mockResponse
            )
          )
        );
      });
    });
    describe("Failing calls", () => {
      const generator = callUpdateSupplierCorrectiveActionRequest(
        updateSupplierCorrectiveActionRequest({
          id: 1,
          factory: "/iri/1",
          iFactor: "IF1",
          shortDescription: "shortDescription",
          description: "description",
          representative: "/iri/1",
          supplierNumber: "/iri/1",
          leader: null,
          issueOrigin: "blah",
          correctiveAction: "blah",
          preventiveAction: "blah",
          commercialAgreement: "blah",
          verificationDescription: "blah",
          conclusion: "blah",
        })
      );
      it("should then fetch the API", () => {
        const id = 1;
        expect(generator.next().value).to.deep.equal(
          call(
            client.put,
            `/quality/supplier_corrective_action_requests/${id}`,
            {
              id: 1,
              factory: "/iri/1",
              iFactor: "IF1",
              shortDescription: "shortDescription",
              description: "description",
              representative: "/iri/1",
              supplierNumber: "/iri/1",
              leader: null,
              issueOrigin: "blah",
              correctiveAction: "blah",
              preventiveAction: "blah",
              commercialAgreement: "blah",
              verificationDescription: "blah",
              conclusion: "blah",
            }
          )
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            violations: [
              {
                propertyPath: "pathLeChien",
                message: "in a bottle",
              },
            ],
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed(
              UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
              error.response
            )
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
