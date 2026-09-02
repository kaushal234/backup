import { call, put, takeLatest } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import {
  MIM_FETCH_MARKET_INTELLIGENCES,
  MIM_WRITE_MARKET_INTELLIGENCE,
} from "../../constants";
import { generateFormErrors } from "../../utils/api";
import { APICallSuccess, APICallFailed } from "../../actions/genericActions";
import { client } from "../../store";
import callGenericGetGenerator from "../common/generator";

export function* callWriteMarketIntelligence({
  payload: { url, body, form },
  type,
}: any): Generator<any, void, any> {
  let errors = {};
  yield put(startSubmit(form));
  try {
    const response = yield call(body.id ? client.put : client.post, url, body);
    yield put(APICallSuccess(type, response));
    yield put(autofill(form, "id", response.data.id));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
    errors = yield call(generateFormErrors, e);
  }
  yield put(stopSubmit(form, errors));
}

export default function* watchSagaForMarketIntelligence() {
  yield takeLatest(MIM_WRITE_MARKET_INTELLIGENCE, callWriteMarketIntelligence);
  yield takeLatest(MIM_FETCH_MARKET_INTELLIGENCES, callGenericGetGenerator);
}
