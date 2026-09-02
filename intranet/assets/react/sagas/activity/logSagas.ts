import { call, put, takeEvery } from "redux-saga/effects";
import { ACTIVITY_FETCH_LOGS } from "../../constants";
import { client } from "../../store";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";

export function* callGetLogs({
  payload: { url, iri },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.get, url);
    yield put(APICallSuccess(type, { ...response, iri }));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export default function* watchSagaForLogs() {
  yield takeEvery(ACTIVITY_FETCH_LOGS, callGetLogs);
}
