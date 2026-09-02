import { call, put, takeLatest } from "redux-saga/effects";
import { autofill } from "redux-form";
import { client } from "../../store";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";
import {
  FINANCE_CREATE_FINANCE_FAMILY,
  FINANCE_DELETE_FINANCE_FAMILY,
  FINANCE_UPDATE_FINANCE_FAMILY,
} from "../../constants";

export function* callUpdateFinanceFamily({
  payload: { url, body },
  type,
  index,
}: any): Generator<any, void, any> {
  try {
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showLoading`,
        true
      )
    );
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showErrorUpdate`,
        false
      )
    );
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showSuccess`,
        false
      )
    );
    const response = yield call(client.put, url, body);
    yield put(APICallSuccess(type, { ...response, index }));
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showLoading`,
        false
      )
    );
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showSuccess`,
        true
      )
    );
    yield response.data.pricings.map((pricing: any) => {
      return put(
        autofill(
          "finance_family_quick_edit",
          `financeFamilies[${index}].pricings[${pricing.sso["@id"]}-${pricing.factory["@id"]}]['@id']`,
          pricing["@id"]
        )
      );
    });
  } catch (e: any) {
    yield put(APICallFailed(type, { ...e.response, index }));
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showLoading`,
        false
      )
    );
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showErrorUpdate`,
        true
      )
    );
  }
}

export function* callCreateFinanceFamily({
  payload: { url, body },
  type,
  index,
}: any): Generator<any, void, any> {
  try {
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showLoading`,
        true
      )
    );
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showSuccess`,
        false
      )
    );
    const response = yield call(client.post, url, body);
    yield put(APICallSuccess(type, { ...response, index }));
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].id`,
        response.data.id
      )
    );
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showLoading`,
        false
      )
    );
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showSuccess`,
        true
      )
    );
    yield response.data.pricings.map((pricing: any) => {
      return put(
        autofill(
          "finance_family_quick_edit",
          `financeFamilies[${index}].pricings[${pricing.sso["@id"]}-${pricing.factory["@id"]}]['@id']`,
          pricing["@id"]
        )
      );
    });
  } catch (e: any) {
    yield put(APICallFailed(type, { ...e.response, index }));
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showLoading`,
        false
      )
    );
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].showErrorCreate`,
        true
      )
    );
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].errorMessage`,
        e.response.data["hydra:description"]
      )
    );
  }
}

export function* callDeleteFinanceFamily({
  payload: { url, index },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.delete, url);
    yield put(APICallSuccess(type, response));
    yield put(
      autofill(
        "finance_family_quick_edit",
        `financeFamilies[${index}].softDeleted`,
        1
      )
    );
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export default function* watchSagaForFinanceFamily() {
  yield takeLatest(FINANCE_UPDATE_FINANCE_FAMILY, callUpdateFinanceFamily);
  yield takeLatest(FINANCE_DELETE_FINANCE_FAMILY, callDeleteFinanceFamily);
  yield takeLatest(FINANCE_CREATE_FINANCE_FAMILY, callCreateFinanceFamily);
}
