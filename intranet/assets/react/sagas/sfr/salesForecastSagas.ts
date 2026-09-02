import {
  all,
  call,
  put,
  takeEvery,
  takeLatest,
  delay,
} from "redux-saga/effects";
import { startSubmit, stopSubmit, autofill } from "redux-form";
import _ from "lodash";
import { client } from "../../store";
import {
  APICallFailed,
  APICallSuccess,
  hideSuccessAlert,
} from "../../actions/genericActions";
import { generateFormErrors } from "../../utils/api";
import {
  SFR_CREATE_MASTER_SALES_FORECAST,
  SFR_GET_SALES_FORECAST_COMMENTS,
  SFR_GET_SALES_FORECAST_VALORIZATION,
  SFR_UPDATE_SALES_FORECAST,
  SFR_UPDATE_SALES_FORECASTS,
  SFR_UPDATE_SALES_FORECASTS_COMPLETED,
} from "../../constants";
import {
  updateSalesForecastsCompleted,
  updateSalesForecast,
} from "../../actions/sfr/sfrActions";
import salesForecastFactory from "../../model/form/sfr_quick_edit/factory";

export function* callUpdateSalesForecast({
  payload: { url, body, errors, form, index },
  type,
}: any): Generator<any, void, any> {
  yield put(startSubmit(form));
  try {
    const response = yield call(client.put, url, body);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
    e.response.data.violations = e.response.data.violations.map(
      (violation: any) => {
        return {
          ...violation,
          propertyPath: `salesForecasts[${index}].${violation.propertyPath}`,
        };
      }
    );
    const error = yield call(generateFormErrors, e);
    errors.salesForecasts = _.merge(
      errors.salesForecasts,
      error.salesForecasts
    );
  }
  yield put(stopSubmit(form, errors));
}

export function* callUpdateSalesForecasts({
  payload: { salesForecasts, errors, form },
}: any): Generator<any, void, any> {
  const tasks: Array<any> = [];
  salesForecasts.forEach((salesForecast: any) => {
    tasks.push(
      call(
        callUpdateSalesForecast,
        updateSalesForecast(
          salesForecastFactory(salesForecast),
          errors,
          form,
          salesForecast.index
        )
      )
    );
  });

  yield all(tasks);

  if (errors.salesForecasts.length === 0) {
    yield put(updateSalesForecastsCompleted());
  }
}

export function* callGetSalesForecastComments({
  payload: { url, sfrIri },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.get, url);
    yield put(APICallSuccess(type, { ...response, sfrIri }));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callCreateMasterSalesForecast({
  payload: { url, body, form, index, allSubmitted, errors = [] },
  type,
}: any): Generator<any, void, any> {
  yield put(startSubmit(form));
  try {
    const response = yield call(client.post, url, body);
    yield put(APICallSuccess(type, { ...response, allSubmitted }));
    yield put(autofill(form, `salesForecasts[${index}].submitted`, true));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
    if (body.salesForecasts.length === 1) {
      e.response.data.violations = e.response.data.violations.map(
        (violation: any) => {
          return {
            ...violation,
            propertyPath: violation.propertyPath.replace("[0]", `[${index}]`),
          };
        }
      );
    }
    const error = yield call(generateFormErrors, e);
    errors = _.merge(errors, error);
  }
  yield put(stopSubmit(form, errors));
}

export function* callHideSuccessMessage() {
  yield delay(2000);
  yield put(hideSuccessAlert("SFR"));
}

export function* callGetValorizationForSalesForecast({
  payload: { url, index },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.get, url);
    yield put(APICallSuccess(type, { ...response, index }));
    yield put(
      autofill(
        "sfr_create_form",
        `salesForecasts[${index}].price`,
        response.data["hydra:member"][0].averagePrice || ""
      )
    );
    yield put(
      autofill(
        "sfr_create_form",
        `salesForecasts[${index}].margin`,
        response.data["hydra:member"][0].averageMargin || ""
      )
    );
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export default function* watchSagaForSalesForecasts() {
  yield takeEvery(SFR_UPDATE_SALES_FORECAST, callUpdateSalesForecast);
  yield takeEvery(SFR_UPDATE_SALES_FORECASTS, callUpdateSalesForecasts);
  yield takeEvery(
    SFR_GET_SALES_FORECAST_COMMENTS,
    callGetSalesForecastComments
  );
  yield takeEvery(
    SFR_CREATE_MASTER_SALES_FORECAST,
    callCreateMasterSalesForecast
  );
  yield takeEvery(
    SFR_GET_SALES_FORECAST_VALORIZATION,
    callGetValorizationForSalesForecast
  );
  yield takeLatest(
    SFR_UPDATE_SALES_FORECASTS_COMPLETED,
    callHideSuccessMessage
  );
}
