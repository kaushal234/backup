import { call, put, takeLatest } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import { APICallSuccess, APICallFailed } from "../../actions/genericActions";
import { client } from "../../store";
import { MIM_WRITE_MARKET_INTELLIGENCE_SUBSCRIPTION } from "../../constants";
import { generateFormErrors } from "../../utils/api";

export function* callWriteMarketIntelligenceSubscription({
  payload: { url, body, form },
  type,
}: any): Generator<any, void, any> {
  let errors = {};
  yield put(startSubmit(form));
  try {
    const response = yield call(client.post, url, body);
    yield put(APICallSuccess(type, response));
    yield put(autofill(form, "id", response.data.id));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
    errors = yield call(generateFormErrors, e);
  }
  yield put(stopSubmit(form, errors));
}

export default function* watchSagaForMarketIntelligenceSubscription() {
  yield takeLatest(
    MIM_WRITE_MARKET_INTELLIGENCE_SUBSCRIPTION,
    callWriteMarketIntelligenceSubscription
  );
}
