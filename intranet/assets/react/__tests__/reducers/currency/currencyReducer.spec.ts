import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/currency/currencyReducer";

const initialState = {
  currencies: [],
};

const fakeCollectionPayload = {
  data: {
    "hydra:member": [
      { "@id": "/foo/1" },
      { "@id": "/foo/2" },
      { "@id": "/foo/3" },
    ],
  },
};

describe("currencyReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle CURRENCY_FETCH_CURRENCIES_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "CURRENCY_FETCH_CURRENCIES_SUCCESS",
        payload: fakeCollectionPayload,
      })
    ).to.deep.equal({
      currencies: [
        { "@id": "/foo/1" },
        { "@id": "/foo/2" },
        { "@id": "/foo/3" },
      ],
    });
  });
});
