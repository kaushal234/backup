import { takeEvery, takeLatest, call, put } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import {
  MIS_ADD_TROUBLE_TICKETS_TO_USER_STORIES,
  MIS_CREATE_SUPPORT_TEAM,
  MIS_FETCH_MODULES,
  MIS_FETCH_TAGS,
  MIS_FETCH_TYPE,
  MIS_FETCH_TYPES,
  MIS_GET_TROUBLE_TICKETS_BY_MODULE,
  MIS_UPDATE_SUPPORT_TEAM,
  MIS_UPDATE_TROUBLE_TICKET,
  MIS_UPDATE_TROUBLE_TICKET_OWNERS,
} from "../../constants";
import callGenericGetGenerator from "../common/generator";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";
import { fetchAPI, generateFormErrors } from "../../utils/api";
import { client } from "../../store";

export function* callType({
  payload: {
    request: { url },
    form,
  },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(fetchAPI, url);
    yield put(APICallSuccess(type, response));
    yield put(autofill(form, "indiceFactor", response.data.indiceFactor));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callUpdateTroubleTicket({
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

export function* callGetTroubleTicketsByModule({
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

export function* callAddTroubleTicketToUserStories({
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

export function* callCreateSupportTeam({
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

export function* callEditSupportTeam({
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

export default function* watchSagaForMis() {
  yield takeEvery(MIS_FETCH_TAGS, callGenericGetGenerator);
  yield takeEvery(MIS_FETCH_MODULES, callGenericGetGenerator);
  yield takeEvery(MIS_FETCH_TYPES, callGenericGetGenerator);
  yield takeEvery(MIS_FETCH_TYPE, callType);
  yield takeEvery(MIS_UPDATE_TROUBLE_TICKET, callUpdateTroubleTicket);
  yield takeEvery(MIS_UPDATE_TROUBLE_TICKET_OWNERS, callUpdateTroubleTicket);
  yield takeLatest(
    MIS_GET_TROUBLE_TICKETS_BY_MODULE,
    callGetTroubleTicketsByModule
  );
  yield takeLatest(
    MIS_ADD_TROUBLE_TICKETS_TO_USER_STORIES,
    callAddTroubleTicketToUserStories
  );
  yield takeLatest(MIS_CREATE_SUPPORT_TEAM, callCreateSupportTeam);
  yield takeLatest(MIS_UPDATE_SUPPORT_TEAM, callEditSupportTeam);
}
