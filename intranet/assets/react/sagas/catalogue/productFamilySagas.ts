import { takeLatest } from "redux-saga/effects";
import { CATALOGUE_FETCH_PRODUCT_FAMILIES } from "../../constants";
import callGenericGetGenerator from "../common/generator";

export default function* watchSagaForProductFamilies() {
  yield takeLatest(CATALOGUE_FETCH_PRODUCT_FAMILIES, callGenericGetGenerator);
}
