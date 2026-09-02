import { takeLatest } from "redux-saga/effects";
import { INCOTERM_FETCH_TERMS_OF_DELIVERIES } from "../../constants";
import callGenericGetGenerator from "../common/generator";

export default function* watchSagaForTermsOfDelivery() {
  yield takeLatest(INCOTERM_FETCH_TERMS_OF_DELIVERIES, callGenericGetGenerator);
}
