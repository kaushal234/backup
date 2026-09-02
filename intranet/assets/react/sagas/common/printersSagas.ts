import { takeEvery } from "redux-saga/effects";
import { COMMON_FETCH_PRINTERS } from "../../constants";
import callGenericGetGenerator from "./generator";

export default function* watchSagaForPrinters() {
  yield takeEvery(COMMON_FETCH_PRINTERS, callGenericGetGenerator);
}
