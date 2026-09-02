import { takeLatest } from "redux-saga/effects";
import { EXTRANET_USER_FETCH_EXTRANET_USERS } from "../../constants";
import callGenericGetGenerator from "../common/generator";

export default function* watchSagaForExtranetUsers() {
  yield takeLatest(EXTRANET_USER_FETCH_EXTRANET_USERS, callGenericGetGenerator);
}
