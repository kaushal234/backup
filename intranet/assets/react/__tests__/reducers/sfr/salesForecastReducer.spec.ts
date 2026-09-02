import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/sfr/salesForecastReducer";

const initialState = {
  comments: [],
  salesForecasts: [],
  sfrValorization: [],
  showLoading: false,
  showSuccess: false,
};

const fakeItemPayload = {
  data: {
    "@id": "/foo/1",
    bar: "foo",
    baz: 42,
  },
};

describe("salesForecastReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle SFR_UPDATE_SALES_FORECAST_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "SFR_UPDATE_SALES_FORECAST_SUCCESS",
        payload: fakeItemPayload,
      })
    ).to.deep.equal({
      details: {
        "@id": "/foo/1",
        bar: "foo",
        baz: 42,
      },
      comments: [],
      salesForecasts: [],
      sfrValorization: [],
      showLoading: false,
      showSuccess: false,
    });
  });
  it("should handle SFR_UPDATE_SALES_FORECASTS_COMPLETED", () => {
    expect(
      reducer(initialState, { type: "SFR_UPDATE_SALES_FORECASTS_COMPLETED" })
    ).to.deep.equal({
      comments: [],
      salesForecasts: [],
      sfrValorization: [],
      showLoading: false,
      showSuccess: true,
    });
  });
  it("should handle SFR_HIDE_SUCCESS_ALERT", () => {
    expect(
      reducer(initialState, { type: "SFR_HIDE_SUCCESS_ALERT" })
    ).to.deep.equal({
      comments: [],
      salesForecasts: [],
      sfrValorization: [],
      showLoading: false,
      showSuccess: false,
      redirectFlag: true,
    });
  });
  it("should handle SFR_GET_SALES_FORECAST_COMMENTS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "SFR_GET_SALES_FORECAST_COMMENTS_SUCCESS",
        payload: {
          sfrIri: "/sales/sales_forecasts/5",
          data: { "hydra:member": [{ test: "comment" }] },
        },
      })
    ).to.deep.equal({
      comments: {
        "/sales/sales_forecasts/5": [{ test: "comment" }],
      },
      salesForecasts: [],
      sfrValorization: [],
      showLoading: false,
      showSuccess: false,
    });
  });
  it("should handle SFR_CREATE_MASTER_SALES_FORECAST", () => {
    expect(
      reducer(initialState, { type: "SFR_CREATE_MASTER_SALES_FORECAST" })
    ).to.deep.equal({
      comments: [],
      salesForecasts: [],
      sfrValorization: [],
      showLoading: true,
      showSuccess: false,
    });
  });
  it("should handle SFR_CREATE_MASTER_SALES_FORECAST_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "SFR_CREATE_MASTER_SALES_FORECAST_SUCCESS",
        payload: { allSubmitted: true },
      })
    ).to.deep.equal({
      comments: [],
      salesForecasts: [],
      sfrValorization: [],
      showLoading: false,
      showSuccess: true,
    });
  });
});
