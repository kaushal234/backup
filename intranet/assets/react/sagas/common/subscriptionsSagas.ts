import { call, put, takeEvery } from "redux-saga/effects";
import { autofill } from "redux-form";
import {
  COMMON_CREATE_SUBSCRIPTION,
  COMMON_DELETE_SUBSCRIPTION,
  COMMON_FETCH_SUBSCRIPTIONS,
} from "../../constants";
import { APICallSuccess, APICallFailed } from "../../actions/genericActions";
import { client } from "../../store";

export function* callCreateSubscription({
  payload: {
    form,
    request: { url, body },
  },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.post, url, body);
    yield put(APICallSuccess(type, response));
    if (form !== null) {
      yield put(autofill(form, "user", null));
    }
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callGetSubscriptions({
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

export function* callDeleteSubscription({
  payload: { resourceIri, subscriptionIri },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.delete, subscriptionIri);
    yield put(
      APICallSuccess(type, { ...response, resourceIri, subscriptionIri })
    );
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export default function* watchSagaForComments() {
  yield takeEvery(COMMON_FETCH_SUBSCRIPTIONS, callGetSubscriptions);
  yield takeEvery(COMMON_CREATE_SUBSCRIPTION, callCreateSubscription);
  yield takeEvery(COMMON_DELETE_SUBSCRIPTION, callDeleteSubscription);
}
