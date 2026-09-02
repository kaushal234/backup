import { takeLatest, put, call } from "redux-saga/effects";
import { EVENT_ADD_EVENT, EVENT_UPDATE_EVENT } from "../../../constants";
import { client } from "../../../store";
import { APICallFailed, APICallSuccess } from "../../../actions/genericActions";

export function* callCreateEvent({
  payload: {
    request: { url, body },
  },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.post, url, body);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callUpdateEvent({
  payload: {
    request: { url, body },
  },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.put, url, body);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export default function* watchSagaForEvent() {
  yield takeLatest(EVENT_ADD_EVENT, callCreateEvent);
  yield takeLatest(EVENT_UPDATE_EVENT, callUpdateEvent);
}
