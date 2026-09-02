import { call, put, takeEvery } from "redux-saga/effects";
import { autofill } from "redux-form";
import { ERP_FETCH_ITEM, ERP_FETCH_ITEMS } from "../../constants";
import { fetchAPI } from "../../utils/api";
import { APICallSuccess, APICallFailed } from "../../actions/genericActions";

export function* callFetchItems({
  payload: {
    request: { url },
  },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(fetchAPI, url);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callFetchItem({
  payload: {
    index,
    request: { url },
  },
  type,
  form,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(fetchAPI, url);
    yield put(APICallSuccess(type, response));
    yield put(
      autofill(
        form,
        `parts[${index}].unitOfMeasure`,
        response.data.unitOfMeasure
      )
    );
    yield put(autofill(form, `parts[${index}].quantity`, 1));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export default function* watchSagaForPart() {
  yield takeEvery(ERP_FETCH_ITEMS, callFetchItems);
  yield takeEvery(ERP_FETCH_ITEM, callFetchItem);
}
