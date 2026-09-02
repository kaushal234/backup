import { takeLatest } from "redux-saga/effects";
import { ROLE_FETCH_ROLE } from "../../constants";
import callGenericGetGenerator from "../common/generator";

export default function* watchSagaForROLE() {
  yield takeLatest(ROLE_FETCH_ROLE, callGenericGetGenerator);
}
