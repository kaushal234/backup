import { takeLatest } from "redux-saga/effects";
import { APC_FETCH_AIRPORT, APC_FETCH_AIRPORTS } from "../../constants";
import callGenericGetGenerator from "../common/generator";

export default function* watchSagaForAirports() {
  yield takeLatest(
    [APC_FETCH_AIRPORT, APC_FETCH_AIRPORTS],
    callGenericGetGenerator
  );
}
