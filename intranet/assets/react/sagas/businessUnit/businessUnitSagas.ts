import { takeLatest } from "redux-saga/effects";
import { DIRECTORY_FETCH_BUSINESS_UNITS } from "../../constants";
import callGenericGetGenerator from "../common/generator";

export default function* watchSagaForBusinessUnits() {
  yield takeLatest(DIRECTORY_FETCH_BUSINESS_UNITS, callGenericGetGenerator);
}
