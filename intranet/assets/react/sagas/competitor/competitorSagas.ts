import { takeLatest } from "redux-saga/effects";
import {
  COMPETITORS_FETCH_COMPETITOR,
  COMPETITORS_FETCH_COMPETITORS,
} from "../../constants";
import callGenericGetGenerator from "../common/generator";

export default function* watchSagaForCompetitors() {
  yield takeLatest(
    [COMPETITORS_FETCH_COMPETITOR, COMPETITORS_FETCH_COMPETITORS],
    callGenericGetGenerator
  );
}
