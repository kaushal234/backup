import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { client } from "../../../store";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import { updateProduct } from "../../../actions/catalogue/productsActions";
import { callUpdateProduct } from "../../../sagas/catalogue/productsSagas";

describe("productsSagas", () => {
  describe("callUpdateProduct", () => {
    describe("Successful calls", () => {
      const generator = callUpdateProduct(updateProduct({ id: 15 }));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/sales/products/15", { id: 15 })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(APICallSuccess("CATALOGUE_UPDATE_PRODUCT", { data: { id: 15 } }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callUpdateProduct(updateProduct({ id: 15, index: 2 }));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/sales/products/15", { id: 15, index: 2 })
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
            APICallFailed("CATALOGUE_UPDATE_PRODUCT", {
              status: 400,
              data: {
                violations: [
                  {
                    propertyPath: "pathLeChien",
                    message: "in a bottle",
                  },
                ],
              },
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
