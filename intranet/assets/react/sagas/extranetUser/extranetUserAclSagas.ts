import { call, put, takeEvery } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import { client } from "../../store";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";
import { generateFormErrors } from "../../utils/api";
import { EXTRANET_USER_CREATE_EXTRANET_USER_ACLS } from "../../constants";

export function* callCreateExtranetUserAcl({
  payload: { url, body, form },
  type,
}: any): Generator<any, void, any> {
  let errors = {};
  yield put(startSubmit(form));
  try {
    const response = yield call(client.post, url, body);
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

export default function* watchSagaForExtranetUserAcl() {
  yield takeEvery(
    EXTRANET_USER_CREATE_EXTRANET_USER_ACLS,
    callCreateExtranetUserAcl
  );
}
