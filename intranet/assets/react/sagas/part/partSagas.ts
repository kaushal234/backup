import { call, put, takeEvery } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import { PART_EDIT_PART_OBJECT } from "../../constants";
import { generateFormErrors } from "../../utils/api";
import { APICallSuccess, APICallFailed } from "../../actions/genericActions";
import { client } from "../../store";

export function* callAdminParts({
  payload: { url, body },
  type,
  form,
}: any): Generator<any, void, any> {
  let errors = {};
  yield put(startSubmit(form));
  try {
    const response = yield call(client.put, url, body);
    yield put(APICallSuccess(type, { ...response }));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
    errors = yield call(generateFormErrors, e);
    yield put(
      autofill(form, `errorMessage`, e.response.data["hydra:description"])
    );
  }
  yield put(stopSubmit(form, errors));
}

export default function* watchSagaForSparePartsRequest() {
  yield takeEvery(PART_EDIT_PART_OBJECT, callAdminParts);
}
