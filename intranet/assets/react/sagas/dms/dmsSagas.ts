import { takeLatest } from "redux-saga/effects";
import { DMS_FETCH_DMS } from "../../constants";
import callGenericGetGenerator from "../common/generator";

export default function* watchSagaForDMS() {
  yield takeLatest(DMS_FETCH_DMS, callGenericGetGenerator);
}
