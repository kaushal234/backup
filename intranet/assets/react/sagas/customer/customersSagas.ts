import { call, put, takeEvery, takeLatest } from "redux-saga/effects";
import { autofill } from "redux-form";
import {
  SALES_CREATE_CUSTOMER_FORM,
  SALES_CRT_FETCH_CUSTOMER,
  SALES_FETCH_CUSTOMER,
  SALES_FETCH_CUSTOMERS,
  SALES_UPDATE_CUSTOMER_WATCH_LIST,
} from "../../constants";
import { fetchAPI } from "../../utils/api";
import { APICallSuccess, APICallFailed } from "../../actions/genericActions";
import { client } from "../../store";
import callGenericGetGenerator from "../common/generator";

export function* callFetchSalesCustomers({
  payload: {
    request: { url, name },
  },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(fetchAPI, url);
    yield put(APICallSuccess(type, { ...response, name }));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callFetchSalesCustomer({
  payload: {
    request: { url },
  },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(fetchAPI, url);
    yield put(APICallSuccess(type, response));
    yield put(
      autofill("customer_relationship_team_form", `salesRepresentative`, {
        value: response.data.mainSalesRepresentative.asm["@id"],
        label: `${response.data.mainSalesRepresentative.asm.lastname} ${response.data.mainSalesRepresentative.asm.firstname}`,
      })
    );
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callCreateCustomerInForm({
  payload: { url, body },
  type,
  form,
  inputName,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.post, url, body);
    yield put(APICallSuccess(type, response));
    yield put(
      autofill(form, inputName, {
        value: response.data["@id"],
        label: response.data.name,
      })
    );
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callUpdateCustomerWatchList({
  payload: { url, body },
  type,
  form,
  customerIndex,
}: any): Generator<any, void, any> {
  const pendingProperty = body.watchList ? "saving" : "deleting";
  yield put(
    autofill(form, `customers[${customerIndex}].${pendingProperty}`, 1)
  );
  try {
    const response = yield call(client.put, url, body);
    yield put(APICallSuccess(type, response));
    if (body.watchList === false) {
      yield put(autofill(form, `customers[${customerIndex}].softDeleted`, 1));
    }
    yield put(
      autofill(form, `customers[${customerIndex}].${pendingProperty}`, 0)
    );
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
    yield put(
      autofill(form, `customers[${customerIndex}].${pendingProperty}`, 0)
    );
  }
}

export default function* watchSagaForSalesCustomers() {
  yield takeLatest(SALES_FETCH_CUSTOMER, callGenericGetGenerator);
  yield takeLatest(SALES_FETCH_CUSTOMERS, callFetchSalesCustomers);
  yield takeLatest(SALES_CREATE_CUSTOMER_FORM, callCreateCustomerInForm);
  yield takeLatest(
    SALES_UPDATE_CUSTOMER_WATCH_LIST,
    callUpdateCustomerWatchList
  );
  yield takeEvery(SALES_CRT_FETCH_CUSTOMER, callFetchSalesCustomer);
}
