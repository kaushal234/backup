import { call, put, takeLatest } from "redux-saga/effects";
import {
  USER_FETCH_CONNECTED_USER,
  USER_FETCH_USERS,
  SUCCESS,
  FAILED,
} from "../../constants";
import { fetchAPI } from "../../utils/api";

export function* callFetchConnectedUser({
  payload: {
    request: { url },
  },
}: any): Generator<any, void, any> {
  try {
    const response = yield call(fetchAPI, url);
    yield put({
      type: USER_FETCH_CONNECTED_USER + SUCCESS,
      payload: response,
    });
  } catch (e: any) {
    yield put({
      type: USER_FETCH_CONNECTED_USER + FAILED,
      payload: e.response,
    });
  }
}

export function* callFetchUsersList({
  payload: {
    request: { url },
  },
}: any): Generator<any, void, any> {
  try {
    const response = yield call(fetchAPI, url);
    yield put({
      type: USER_FETCH_USERS + SUCCESS,
      payload: response,
    });
  } catch (e: any) {
    yield put({
      type: USER_FETCH_USERS + FAILED,
      payload: e.response,
    });
  }
}

export default function* watchSagaForUsers() {
  yield takeLatest(USER_FETCH_CONNECTED_USER, callFetchConnectedUser);
  yield takeLatest(USER_FETCH_USERS, callFetchUsersList);
}
