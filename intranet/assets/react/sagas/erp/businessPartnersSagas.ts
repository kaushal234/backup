import { takeLatest } from "redux-saga/effects";
import {
  ERP_FETCH_BUSINESS_PARTNER,
  ERP_FETCH_BUSINESS_PARTNERS,
  ERP_FETCH_SUPPLIERS,
} from "../../constants";
import callGenericGetGenerator from "../common/generator";

export default function* watchSagaForBusinessPartners() {
  yield takeLatest(ERP_FETCH_BUSINESS_PARTNER, callGenericGetGenerator);
  yield takeLatest(ERP_FETCH_BUSINESS_PARTNERS, callGenericGetGenerator);
  yield takeLatest(ERP_FETCH_SUPPLIERS, callGenericGetGenerator);
}
