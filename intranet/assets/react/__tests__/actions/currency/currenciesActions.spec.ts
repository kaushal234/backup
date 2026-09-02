import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/currency/currenciesActions";

describe("currenciesActions", () => {
  describe("fetchCurrencies", () => {
    it("should create a fetch action", () => {
      const url = "/finance/currencies";
      const expectedAction = {
        type: "CURRENCY_FETCH_CURRENCIES",
        payload: {
          request: {
            url,
          },
        },
      };
      expect(actions.fetchCurrencies()).to.deep.equal(expectedAction);
    });
  });
});
