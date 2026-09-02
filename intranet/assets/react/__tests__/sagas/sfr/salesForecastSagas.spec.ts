import { describe, it } from "mocha";
import { expect } from "chai";
import { all, call, put, delay } from "redux-saga/effects";
import { startSubmit, stopSubmit } from "redux-form";
import {
  APICallSuccess,
  APICallFailed,
  hideSuccessAlert,
} from "../../../actions/genericActions";
import { generateFormErrors } from "../../../utils/api";
import { client } from "../../../store";
import {
  getSalesForecastComments,
  updateSalesForecast,
  updateSalesForecasts,
} from "../../../actions/sfr/sfrActions";
import {
  callUpdateSalesForecast,
  callUpdateSalesForecasts,
  callHideSuccessMessage,
  callGetSalesForecastComments,
} from "../../../sagas/sfr/salesForecastSagas";
import salesForecastFactory from "../../../model/form/sfr_quick_edit/factory";

describe("salesForecastSagas", () => {
  describe("callUpdateSalesForecast", () => {
    describe("Successful calls", () => {
      const generator = callUpdateSalesForecast(
        updateSalesForecast({ id: 15 }, { salesForecasts: [] }, "form_name", 2)
      );

      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(
          put(startSubmit("form_name"))
        );
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/sales/sales_forecasts/15", { id: 15 })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(APICallSuccess("SFR_UPDATE_SALES_FORECAST", { data: { id: 15 } }))
        );
      });
      it("should then stop form submission", () => {
        expect(generator.next().value).to.deep.equal(
          put(stopSubmit("form_name", { salesForecasts: [] }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const errors = { salesForecasts: [] };
      const generator = callUpdateSalesForecast(
        updateSalesForecast({ id: 15, index: 2 }, errors, "form_name", 2)
      );
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

      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(
          put(startSubmit("form_name"))
        );
      });
      it("should then fetch the API for the first SFR", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/sales/sales_forecasts/15", { id: 15, index: 2 })
        );
      });
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed("SFR_UPDATE_SALES_FORECAST", {
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
      it("should then generate errors", () => {
        expect(generator.next().value).to.deep.equal(
          call(generateFormErrors, error)
        );
      });
      it("should then stop form submission", () => {
        expect(
          generator.next({
            salesForecasts: [
              undefined,
              undefined,
              { pathLeChien: "in a bottle" },
            ],
          }).value
        ).to.deep.equal(
          put(
            stopSubmit("form_name", {
              salesForecasts: [
                undefined,
                undefined,
                { pathLeChien: "in a bottle" },
              ],
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("callUpdateSalesForecasts", () => {
      describe("Successful calls", () => {
        const generator = callUpdateSalesForecasts(
          updateSalesForecasts(
            [{ id: 15 }, { id: 16 }],
            { salesForecasts: [] },
            "form_name"
          )
        );
        it("should call multiple times the other saga", () => {
          expect(generator.next().value).to.deep.equal(
            all([
              call(
                callUpdateSalesForecast,
                updateSalesForecast(
                  salesForecastFactory({ id: 15 }),
                  { salesForecasts: [] },
                  "form_name"
                )
              ),
              call(
                callUpdateSalesForecast,
                updateSalesForecast(
                  salesForecastFactory({ id: 16 }),
                  { salesForecasts: [] },
                  "form_name"
                )
              ),
            ])
          );
        });
        it("should then dispatch a success event", () => {
          expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
            put({ type: "SFR_UPDATE_SALES_FORECASTS_COMPLETED" })
          );
        });
        it("should then be finished", () => {
          expect(generator.next().done).to.be.true;
        });
      });
      describe("One API call fails", () => {
        const generator = callUpdateSalesForecasts(
          updateSalesForecasts(
            [{ id: 15 }, { id: 16 }],
            { salesForecasts: [{ omg: "it failed 😱" }] },
            "form_name"
          )
        );
        it("should then fetch the API", () => {
          expect(generator.next().value).to.deep.equal(
            all([
              call(
                callUpdateSalesForecast,
                updateSalesForecast(
                  salesForecastFactory({ id: 15 }),
                  { salesForecasts: [{ omg: "it failed 😱" }] },
                  "form_name"
                )
              ),
              call(
                callUpdateSalesForecast,
                updateSalesForecast(
                  salesForecastFactory({ id: 16 }),
                  { salesForecasts: [{ omg: "it failed 😱" }] },
                  "form_name"
                )
              ),
            ])
          );
        });
        it("should then be finished", () => {
          expect(generator.next().done).to.be.true;
        });
      });
    });
    describe("callHideSuccessMessage", () => {
      const generator = callHideSuccessMessage();

      it("should first wait 3seconds", () => {
        expect(generator.next().value).to.deep.equal(delay(2000));
      });
      it("should then dispatch a hide event", () => {
        expect(generator.next().value).to.deep.equal(
          put(hideSuccessAlert("SFR"))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("callGetSalesForecastComments", () => {
      describe("Successful call", () => {
        const generator = callGetSalesForecastComments(
          getSalesForecastComments({ "@id": "/sales/sales_forecasts/69" })
        );
        it("should first fetch the API", () => {
          expect(generator.next().value).to.deep.equal(
            call(
              client.get,
              "/comments?resource=/sales/sales_forecasts/69&pagination=false"
            )
          );
        });
        it("should then dispatch a success event", () => {
          expect(
            generator.next({ data: { "hydra:member": ["foo", "bar"] } }).value
          ).to.deep.equal(
            put(
              APICallSuccess("SFR_GET_SALES_FORECAST_COMMENTS", {
                data: { "hydra:member": ["foo", "bar"] },
                sfrIri: "/sales/sales_forecasts/69",
              })
            )
          );
        });
        it("should then be finished", () => {
          expect(generator.next().done).to.be.true;
        });
      });
      describe("Successful call", () => {
        const generator = callGetSalesForecastComments(
          getSalesForecastComments({ "@id": "/sales/sales_forecasts/69" })
        );
        it("should first fetch the API", () => {
          expect(generator.next().value).to.deep.equal(
            call(
              client.get,
              "/comments?resource=/sales/sales_forecasts/69&pagination=false"
            )
          );
        });
        const error = { response: "test" };
        it("should then dispatch a failed event", () => {
          expect(generator.throw(error).value).to.deep.equal(
            put(APICallFailed("SFR_GET_SALES_FORECAST_COMMENTS", "test"))
          );
        });
        it("should then be finished", () => {
          expect(generator.next().done).to.be.true;
        });
      });
    });
  });
});
