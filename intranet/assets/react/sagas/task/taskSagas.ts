import { call, put, takeLatest } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import { client } from "../../store";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";
import { CREATE_TASK, EDIT_TASK } from "../../constants";
import { generateFormErrors } from "../../utils/api";

export function* callCreateTask({
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
export function* callEditTask({
  payload: { url, body, form },
  type,
}: any): Generator<any, void, any> {
  let errors = {};
  yield put(startSubmit(form));
  try {
    const response = yield call(client.put, url, body);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
    errors = yield call(generateFormErrors, e);
  }
  yield put(stopSubmit(form, errors));
}

export default function* watchSagaForTask() {
  yield takeLatest(CREATE_TASK, callCreateTask);
  yield takeLatest(EDIT_TASK, callEditTask);
}
