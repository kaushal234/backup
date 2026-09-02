import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill } from "redux-form";
import { client } from "../../../store";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import { updateProductManufacturing } from "../../../actions/manufacturing/productManufacturingsActions";
import { callUpdateProductManufacturings } from "../../../sagas/productManufacturing/productManufacturingSagas";

describe("productManufacturingSagas", () => {
  describe("callUpdateProductManufacturings", () => {
    describe("Successful calls", () => {
      const generator = callUpdateProductManufacturings(
        updateProductManufacturing({ id: 15 }, 2)
      );

      it("should first autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "product_manufacturing_form",
              "products[2].showLoading",
              true
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "product_manufacturing_form",
              "products[2].showError",
              false
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "product_manufacturing_form",
              "products[2].showSuccess",
              false
            )
          )
        );
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            client.put,
            "sales/products/15?normalization_groups_override[]=product_manufacturing",
            { id: 15 }
          )
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(
            APICallSuccess("PRODUCT_MANUFACTURING_UPDATE_PRODUCT", {
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
              "product_manufacturing_form",
              "products[2].showLoading",
              false
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "product_manufacturing_form",
              "products[2].showSuccess",
              true
            )
          )
        );
      });
    });

    describe("Failing calls", () => {
      const generator = callUpdateProductManufacturings(
        updateProductManufacturing({ id: 15 }, 2)
      );

      it("should first autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "product_manufacturing_form",
              "products[2].showLoading",
              true
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "product_manufacturing_form",
              "products[2].showError",
              false
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "product_manufacturing_form",
              "products[2].showSuccess",
              false
            )
          )
        );
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            client.put,
            "sales/products/15?normalization_groups_override[]=product_manufacturing",
            { id: 15 }
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
          index: 2,
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed("PRODUCT_MANUFACTURING_UPDATE_PRODUCT", {
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
              "product_manufacturing_form",
              "products[2].showLoading",
              false
            )
          )
        );
        expect(generator.next().value).to.deep.equal(
          put(
            autofill(
              "product_manufacturing_form",
              "products[2].showError",
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
});
