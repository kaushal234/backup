import { call, put, takeLatest } from "redux-saga/effects";
import {
  NON_CONFORMITY_FETCH_NON_CONFORMITIES,
  SUCCESS,
  FAILED,
} from "../../constants";

import { fetchAPI } from "../../utils/api";

export function* callFetchNonConformitiesList({
  payload: {
    request: { url },
  },
}: any): Generator<any, void, any> {
  try {
    const response = yield call(fetchAPI, url);
    yield put({
      type: NON_CONFORMITY_FETCH_NON_CONFORMITIES + SUCCESS,
      payload: response,
    });
  } catch (e: any) {
    yield put({
      type: NON_CONFORMITY_FETCH_NON_CONFORMITIES + FAILED,
      payload: e.response,
    });
  }
}

export default function* watchSagaForUsers() {
  yield takeLatest(
    NON_CONFORMITY_FETCH_NON_CONFORMITIES,
    callFetchNonConformitiesList
  );
}
