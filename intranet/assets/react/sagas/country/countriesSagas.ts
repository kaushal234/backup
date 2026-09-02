import { takeLatest } from "redux-saga/effects";
import { API_FETCH_COUNTRY, API_FETCH_COUNTRIES } from "../../constants";
import callGenericGetGenerator from "../common/generator";

export default function* watchSagaForCountries() {
  yield takeLatest(
    [API_FETCH_COUNTRY, API_FETCH_COUNTRIES],
    callGenericGetGenerator
  );
}
