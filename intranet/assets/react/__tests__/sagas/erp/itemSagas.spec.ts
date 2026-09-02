import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill } from "redux-form";
import { fetchAPI } from "../../../utils/api";
import { APICallSuccess } from "../../../actions/genericActions";
import { fetchItem } from "../../../actions/erp/itemsAction";
import { callFetchItem } from "../../../sagas/erp/itemSagas";

describe("itemSagas", () => {
  describe("callFetchItem", () => {
    describe("Successful calls", () => {
      const generator = callFetchItem(fetchItem(0, "12345", 540, "form"));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(fetchAPI, "/ion/items/item=12345;site=540")
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({ data: { unitOfMeasure: "EA" } }).value
        ).to.deep.equal(
          put(
            APICallSuccess("ERP_FETCH_ITEM", { data: { unitOfMeasure: "EA" } })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "parts[0].unitOfMeasure", "EA"))
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "parts[0].quantity", 1))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
