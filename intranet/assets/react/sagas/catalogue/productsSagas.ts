import { call, put, takeLatest } from "redux-saga/effects";
import { client } from "../../store";
import {
  CATALOGUE_FETCH_PRODUCT,
  CATALOGUE_FETCH_PRODUCTS,
  CATALOGUE_UPDATE_PRODUCT,
} from "../../constants";
import { APICallSuccess, APICallFailed } from "../../actions/genericActions";
import callGenericGetGenerator from "../common/generator";

export function* callUpdateProduct({
  payload: { url, body },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.put, url, body);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export default function* watchSagaForProducts() {
  yield takeLatest(
    [CATALOGUE_FETCH_PRODUCT, CATALOGUE_FETCH_PRODUCTS],
    callGenericGetGenerator
  );
  yield takeLatest(CATALOGUE_UPDATE_PRODUCT, callUpdateProduct);
}
