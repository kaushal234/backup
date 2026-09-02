import { call, put, takeLatest } from "redux-saga/effects";
import { autofill } from "redux-form";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";
import { API_FETCH_ER, API_UPDATE_ER } from "../../constants";
import { client } from "../../store";
import callGenericGetGenerator from "../common/generator";

export function* callUpdateEquipmentRecord({
  payload: {
    request: { url, body },
  },
  type,
  index,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.put, url, body);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
    yield put(
      autofill(
        "odp_line_edit_form",
        `equipmentRecords[${index}].errorMessage`,
        e.response.data["hydra:description"]
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

export default function* watchSagaForEquipmentRecords() {
  yield takeLatest(API_FETCH_ER, callGenericGetGenerator);
  yield takeLatest(API_UPDATE_ER, callUpdateEquipmentRecord);
}
