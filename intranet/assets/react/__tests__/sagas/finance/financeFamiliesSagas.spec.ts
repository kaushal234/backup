import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill } from "redux-form";
import { client } from "../../../store";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import {
  writeFinanceFamily,
  deleteFinanceFamily,
} from "../../../actions/finance/financeFamiliesActions";
import {
  callCreateFinanceFamily,
  callDeleteFinanceFamily,
  callUpdateFinanceFamily,
} from "../../../sagas/finance/financeFamiliesSagas";

describe("financeFamiliesSagas", () => {
  describe("callUpdateFinanceFamily", () => {
    describe("Successful calls", () => {
      const generator = callUpdateFinanceFamily(
        writeFinanceFamily({ id: 15 }, 2)
      );

      it("should first autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[2].showLoading",
              true
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[2].showErrorUpdate",
              false
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[2].showSuccess",
              false
            )
          )
        );
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/finance/finance_families/15", { id: 15 })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(
            APICallSuccess("FINANCE_UPDATE_FINANCE_FAMILY", {
              data: { id: 15 },
              index: 2,
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[2].showLoading",
              false
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[2].showSuccess",
              true
            )
          )
        );
      });
    });

    describe("Failing calls", () => {
      const generator = callUpdateFinanceFamily(
        writeFinanceFamily({ id: 15 }, 2)
      );

      it("should first autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[2].showLoading",
              true
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[2].showErrorUpdate",
              false
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[2].showSuccess",
              false
            )
          )
        );
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/finance/finance_families/15", { id: 15 })
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
          index: 2,
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed("FINANCE_UPDATE_FINANCE_FAMILY", {
              status: 400,
              data: {
                violations: [
                  {
                    propertyPath: "pathLeChien",
                    message: "in a bottle",
                  },
                ],
              },
              index: 2,
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[2].showLoading",
              false
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[2].showErrorUpdate",
              true
            )
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callDeleteFinanceFamily", () => {
    describe("Successful calls", () => {
      const generator = callDeleteFinanceFamily(deleteFinanceFamily(15, 25));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.delete, "/finance/finance_families/15")
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: {} }).value).to.deep.equal(
          put(APICallSuccess("FINANCE_DELETE_FINANCE_FAMILY", { data: {} }))
        );
      });
      it("should then dispatch an autofill event", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              `financeFamilies[25].softDeleted`,
              1
            )
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callDeleteFinanceFamily(deleteFinanceFamily(15));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.delete, "/finance/finance_families/15")
        );
      });
      const error = { response: { data: "test" } };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("FINANCE_DELETE_FINANCE_FAMILY", { data: "test" }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callCreateFinanceFamily", () => {
    describe("Successful creation calls", () => {
      const generator = callCreateFinanceFamily(
        writeFinanceFamily({ name: "Test" }, 12)
      );

      it("should first autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[12].showLoading",
              true
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[12].showSuccess",
              false
            )
          )
        );
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/finance/finance_families", { name: "Test" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({
            data: { id: 9, name: "Test", archived: false },
            index: 12,
          }).value
        ).to.deep.equal(
          put(
            APICallSuccess("FINANCE_CREATE_FINANCE_FAMILY", {
              data: { id: 9, name: "Test", archived: false },
              index: 12,
            })
          )
        );
      });
      it("should then dispatch an autofill event", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill("finance_family_quick_edit", `financeFamilies[12].id`, 9)
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[12].showLoading",
              false
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[12].showSuccess",
              true
            )
          )
        );
      });
    });
    describe("Failing calls", () => {
      const generator = callCreateFinanceFamily(
        writeFinanceFamily({ name: "Fail test" }, 12)
      );

      it("should first autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[12].showLoading",
              true
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[12].showSuccess",
              false
            )
          )
        );
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/finance/finance_families", { name: "Fail test" })
        );
      });
      const error = {
        response: { data: { "hydra:description": "ECHEC" }, index: 12 },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed("FINANCE_CREATE_FINANCE_FAMILY", {
              data: { "hydra:description": "ECHEC" },
              index: 12,
            })
          )
        );
      });
      it("should first autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[12].showLoading",
              false
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[12].showErrorCreate",
              true
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "finance_family_quick_edit",
              "financeFamilies[12].errorMessage",
              "ECHEC"
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
