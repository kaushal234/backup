import { autofill } from "redux-form";
import { call, put, takeLatest, all } from "redux-saga/effects";
import { client } from "../../store";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";
import { PRODUCT_MANUFACTURING_UPDATE_PRODUCT } from "../../constants";

export function* callUpdateProductManufacturings({
  payload: { url, body },
  type,
  index,
}: any): Generator<any, void, any> {
  try {
    yield put(
      autofill(
        "product_manufacturing_form",
        `products[${index}].showLoading`,
        true
      )
    );
    yield put(
      autofill(
        "product_manufacturing_form",
        `products[${index}].showError`,
        false
      )
    );
    yield put(
      autofill(
        "product_manufacturing_form",
        `products[${index}].showSuccess`,
        false
      )
    );
    const response = yield call(client.put, url, body);
    yield put(APICallSuccess(type, { ...response, index }));
    yield put(
      autofill(
        "product_manufacturing_form",
        `products[${index}].showLoading`,
        false
      )
    );
    yield put(
      autofill(
        "product_manufacturing_form",
        `products[${index}].showSuccess`,
        true
      )
    );
    yield all(
      response.data.productManufacturings.map((productManufacturing: any) => {
        const year = new Date(productManufacturing.effectiveAt).getFullYear();
        return put(
          autofill(
            "product_manufacturing_form",
            `products[${index}].productManufacturings[${productManufacturing.factory["@id"]}-${productManufacturing.product}-${year}]['@id']`,
            productManufacturing["@id"]
          )
        );
      })
    );
    yield all(
      response.data.productManufacturings.map((productManufacturing: any) => {
        const year = new Date(productManufacturing.effectiveAt).getFullYear();
        return put(
          autofill(
            "product_manufacturing_form",
            `products[${index}].productManufacturings[${productManufacturing.factory["@id"]}-${productManufacturing.product}-${year}]['effectiveAt']`,
            productManufacturing.effectiveAt
          )
        );
      })
    );
  } catch (e: any) {
    yield put(APICallFailed(type, { ...e.response, index }));
    yield put(
      autofill(
        "product_manufacturing_form",
        `products[${index}].showLoading`,
        false
      )
    );
    yield put(
      autofill(
        "product_manufacturing_form",
        `products[${index}].showError`,
        true
      )
    );
  }
}

export default function* watchSagaForProductManufacturings() {
  yield takeLatest(
    PRODUCT_MANUFACTURING_UPDATE_PRODUCT,
    callUpdateProductManufacturings
  );
}
