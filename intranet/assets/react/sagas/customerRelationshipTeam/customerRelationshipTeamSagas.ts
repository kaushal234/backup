import { call, put, takeEvery } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import { client } from "../../store";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";
import { generateFormErrors } from "../../utils/api";
import {
  SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM,
  SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM,
} from "../../constants";

export function* callCreateCustomerRelationshipTeam({
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
    yield put(
      autofill(form, `errorMessage`, e.response.data["hydra:description"])
    );
  }
  yield put(stopSubmit(form, errors));
}

export function* callEditCustomerRelationshipTeam({
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
    yield put(
      autofill(form, `errorMessage`, e.response.data["hydra:description"])
    );
  }
  yield put(stopSubmit(form, errors));
}

export default function* watchSagaForCustomerRelationshipTeam() {
  yield takeEvery(
    SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM,
    callCreateCustomerRelationshipTeam
  );
  yield takeEvery(
    SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM,
    callEditCustomerRelationshipTeam
  );
}
