import { call, put, takeEvery } from "redux-saga/effects";
import {
  ACTIVITY_CREATE_COMMENT,
  ACTIVITY_FETCH_COMMENTS,
} from "../../constants";
import { APICallSuccess, APICallFailed } from "../../actions/genericActions";
import { getComments } from "../../actions/activity/commentsActions";
import { client } from "../../store";

export function* callCreateComment({
  payload: {
    request: { url, body },
  },
  type,
}: any): Generator<any, void, any> {
  try {
    const formData = new FormData();
    formData.append("resource", body.resource);
    formData.append("message", body.message);
    formData.append("file", body.file);
    if (body.discriminator) {
      formData.append("discriminator", body.discriminator);
    }
    if (body?.public !== undefined) {
      formData.append("public", body.public);
    }
    if (body.metadata) {
      formData.append("metadata", JSON.stringify(body.metadata));
    }
    const response = yield call(client.post, url, formData);
    yield put(APICallSuccess(type, { ...response, iri: body.resource }));
    yield put(getComments(body.resource));
  } catch (e: Error | any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callGetComments({
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

export default function* watchSagaForComments() {
  yield takeEvery(ACTIVITY_FETCH_COMMENTS, callGetComments);
  yield takeEvery(ACTIVITY_CREATE_COMMENT, callCreateComment);
}
