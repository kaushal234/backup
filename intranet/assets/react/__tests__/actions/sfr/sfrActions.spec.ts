import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/sfr/sfrActions";

describe("sfrActions", () => {
  describe("updateSalesForecast", () => {
    it("should create an update action", () => {
      const expectedAction = {
        type: "SFR_UPDATE_SALES_FORECAST",
        payload: {
          form: "quick_edit",
          url: "/sales/sales_forecasts/68",
          body: { id: 68, foo: "bar" },
          index: 56,
          errors: { salesForecasts: [] },
        },
      };
      expect(
        actions.updateSalesForecast(
          { id: 68, foo: "bar" },
          { salesForecasts: [] },
          "quick_edit",
          56
        )
      ).to.deep.equal(expectedAction);
    });
  });
  describe("updateSalesForecasts", () => {
    it("should create an update action", () => {
      const expectedAction = {
        type: "SFR_UPDATE_SALES_FORECASTS",
        payload: {
          form: "quick_edit",
          salesForecasts: [
            { id: 68, foo: "bar" },
            { id: 69, foo: "barbar" },
          ],
          errors: { salesForecasts: [] },
        },
      };
      expect(
        actions.updateSalesForecasts(
          [
            { id: 68, foo: "bar" },
            { id: 69, foo: "barbar" },
          ],
          { salesForecasts: [] },
          "quick_edit"
        )
      ).to.deep.equal(expectedAction);
    });
  });
  describe("updateSalesForecastsCompleted", () => {
    it("should create an update action", () => {
      const expectedAction = {
        type: "SFR_UPDATE_SALES_FORECASTS_COMPLETED",
      };
      expect(actions.updateSalesForecastsCompleted()).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("getSalesForecastComments", () => {
    it("should create a get comments action", () => {
      const expectedAction = {
        type: "SFR_GET_SALES_FORECAST_COMMENTS",
        payload: {
          url: "/comments?resource=/sales/sales_forecasts/5&pagination=false",
          sfrIri: "/sales/sales_forecasts/5",
        },
      };
      expect(
        actions.getSalesForecastComments({ "@id": "/sales/sales_forecasts/5" })
      ).to.deep.equal(expectedAction);
    });
  });
  describe("writeMasterSalesForecast", () => {
    it("should create a create master sales forecast action", () => {
      const expectedAction = {
        type: "SFR_CREATE_MASTER_SALES_FORECAST",
        payload: {
          form: "form_test",
          url: "/sales/master_sales_forecasts",
          body: {},
          index: 1,
          errors: {},
          allSubmitted: true,
        },
      };
      expect(
        actions.writeMasterSalesForecast({}, "form_test", 1, true, {})
      ).to.deep.equal(expectedAction);
    });
  });
  describe("getValorizationForSalesForecast", () => {
    it("should create a fetch valorization action", () => {
      const expectedAction = {
        type: "SFR_GET_SALES_FORECAST_VALORIZATION",
        payload: {
          url: "/finance/finance_family_pricings?financeFamily=/foo&factory=/bar&sso=/team",
          index: 2,
        },
      };
      expect(
        actions.getValorizationForSalesForecast("/foo", "/bar", "/team", 2)
      ).to.deep.equal(expectedAction);
    });
  });
});
