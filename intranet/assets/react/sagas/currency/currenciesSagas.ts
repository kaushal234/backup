import { takeLatest } from "redux-saga/effects";
import { CURRENCY_FETCH_CURRENCIES } from "../../constants";
import callGenericGetGenerator from "../common/generator";

export default function* watchSagaForCurrencies() {
  yield takeLatest(CURRENCY_FETCH_CURRENCIES, callGenericGetGenerator);
}
