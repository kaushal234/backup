import { call, put, takeEvery } from "redux-saga/effects";
import { TAG_FETCH_LIST } from "../../constants";
import { client } from "../../store";
import { APICallSuccess, APICallFailed } from "../../actions/genericActions";

export function* callGetTags({
  type,
  payload,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.get, payload.url);
    yield put(APICallSuccess(type, response));
  } catch (error: any) {
    yield put(APICallFailed(type, error.response));
  }
}

export default function* watchTagsSagas() {
  yield takeEvery(TAG_FETCH_LIST, callGetTags);
}
