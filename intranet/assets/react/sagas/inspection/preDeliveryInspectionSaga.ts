import { takeLatest, call, put } from "redux-saga/effects";
import { autofill } from "redux-form";
import {
  WRITE_PRE_DELIVERY_INSPECTION,
  PRE_DELIVERY_INSPECTION_STATUS_UPDATE,
} from "../../constants";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";
import { client } from "../../store";

function* writePreDeliveryInspectionSaga({
  payload: {
    request: { url, body, apiMethod },
  },
  type,
  index,
}: any): Generator<any, void, any> {
  try {
    const response =
      apiMethod === "PUT"
        ? yield call(client.put, url, body)
        : yield call(client.post, url, body);
    yield put(APICallSuccess(type, response.data));
  } catch (error: any) {
    yield put(APICallFailed(type, error.response));
    yield put(
      autofill(
        "odp_line_edit_form",
        `equipmentRecords[${index}].errorMessage`,
        error.response.data["hydra:description"]
      )
    );
    yield put(
      autofill(
        "odp_line_edit_form",
        `equipmentRecords[${index}].showError`,
        true
      )
    );
  }
}

function* updatePreDeliveryInspectionStatusSaga({
  payload: {
    request: { url, body },
  },
  type,
  index,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.put, url, body);
    yield put(APICallSuccess(type, response));
  } catch (error: any) {
    yield put(APICallFailed(type, error.response));
    yield put(
      autofill(
        "odp_line_edit_form",
        `equipmentRecords[${index}].errorMessage`,
        error.response.data["hydra:description"]
      )
    );
    yield put(
      autofill(
        "odp_line_edit_form",
        `equipmentRecords[${index}].showError`,
        true
      )
    );
  }
}

// Watcher Saga
export default function* watchSagaForPreDeliveryInspections() {
  yield takeLatest(
    WRITE_PRE_DELIVERY_INSPECTION,
    writePreDeliveryInspectionSaga
  );
  yield takeLatest(
    PRE_DELIVERY_INSPECTION_STATUS_UPDATE,
    updatePreDeliveryInspectionStatusSaga
  );
}
