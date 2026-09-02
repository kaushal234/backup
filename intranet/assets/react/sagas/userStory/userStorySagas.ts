import { autofill, startSubmit, stopSubmit } from "redux-form";
import { call, put, takeLatest } from "redux-saga/effects";
import {
  SPECIFICATION_GET,
  USER_STORY_DELETE,
  USER_STORY_DUPLICATE,
  USER_STORY_EDIT,
  USER_STORY_GET,
  USER_STORY_UPDATE_STATUS,
  USER_STORY_WRITE,
} from "../../constants";
import { client } from "../../store";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";
import { generateFormErrors } from "../../utils/api";

export function* callWriteUserStory({
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

export function* callEditUserStory({
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

export function* callGetSpecification({
  payload: { url },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.get, url);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}
export function* callGetUserStory({
  payload: { url },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.get, url);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}
export function* callDeleteUserStory({
  payload: { url },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.delete, url);
    yield put(APICallSuccess(type, { response, url }));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}
export function* callUpdateStatusUserStory({
  payload: { url, body },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.put, url, body);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callDuplicateUserStory({
  payload: { url, body },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.post, url, body);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export default function* watchSagaForUserStory() {
  yield takeLatest(SPECIFICATION_GET, callGetSpecification);
  yield takeLatest(USER_STORY_WRITE, callWriteUserStory);
  yield takeLatest(USER_STORY_EDIT, callEditUserStory);
  yield takeLatest(USER_STORY_GET, callGetUserStory);
  yield takeLatest(USER_STORY_UPDATE_STATUS, callUpdateStatusUserStory);
  yield takeLatest(USER_STORY_DELETE, callDeleteUserStory);
  yield takeLatest(USER_STORY_DUPLICATE, callDuplicateUserStory);
}
