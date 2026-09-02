import { call, put, takeEvery, takeLatest } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import {
  SPR_CREATE_SB_SPARE_PARTS_REQUEST,
  SPR_CREATE_TOC_SPARE_PARTS_REQUEST,
  SPR_EDIT_SPARE_PARTS_REQUEST_ADDRESS,
  SPR_EDIT_TOC_SPARE_PARTS_REQUEST,
  SPR_FETCH_AIRPORT,
  SPR_FETCH_CONTACT,
  SPR_FETCH_DELIVERY_ADDRESSES,
} from "../../constants";
import { fetchAPI, generateFormErrors } from "../../utils/api";
import { APICallSuccess, APICallFailed } from "../../actions/genericActions";
import { client } from "../../store";

export function* callFetchAirport({
  payload: {
    request: { url },
  },
  type,
  form,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(fetchAPI, url);
    yield put(APICallSuccess(type, response));
    yield put(
      autofill(form, "airport", {
        value: response.data["@id"],
        label: `${response.data.code} - ${response.data.cityName}`,
      })
    );
    yield put(
      autofill(form, "country", {
        value: response.data.country["@id"],
        label: response.data.country.name,
        isoCode2: response.data.country.isoCode2,
      })
    );
    yield put(autofill(form, "city", response.data.cityName));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callFetchContact({
  payload: {
    request: { url },
  },
  type,
  form,
  index = null,
}: any): Generator<any, void, any> {
  const lastnameKey =
    index !== null ? `sparePartsRequests[${index}].lastname` : "lastname";
  const firstnameKey =
    index !== null ? `sparePartsRequests[${index}].firstname` : "firstname";
  const phoneKey =
    index !== null ? `sparePartsRequests[${index}].phone` : "phone";
  const companyKey =
    index !== null ? `sparePartsRequests[${index}].company` : "company";
  try {
    const response = yield call(fetchAPI, url);
    yield put(APICallSuccess(type, response));
    yield put(autofill(form, lastnameKey, response.data.lastname));
    yield put(autofill(form, firstnameKey, response.data.firstname));
    yield put(autofill(form, phoneKey, response.data.phones[0].number || ""));
    yield put(
      autofill(
        form,
        companyKey,
        response.data.extranetUserProfile.customer.name || ""
      )
    );
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callFetchDeliveryAddresses({
  payload: {
    request: { url },
  },
  type,
  discriminator,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(fetchAPI, url);
    yield put(APICallSuccess(type, { ...response, discriminator }));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
  }
}

export function* callCreateTOCSparePartsRequest({
  payload: { url, body, form },
  type,
}: any): Generator<any, void, any> {
  let errors = {};
  yield put(startSubmit(form));
  try {
    const response = yield call(client.post, url, body);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
    errors = yield call(generateFormErrors, e);
    yield put(
      autofill(form, `errorMessage`, e.response.data["hydra:description"])
    );
  }
  yield put(stopSubmit(form, errors));
}

export function* callCreateSBSparePartsRequest({
  payload: { url, body, form },
  type,
  index,
}: any): Generator<any, void, any> {
  let errors = {};
  yield put(startSubmit(form));
  try {
    const response = yield call(client.post, url, body);
    yield put(APICallSuccess(type, { ...response, index }));
    yield put(autofill(form, `sparePartsRequests[${index}].submitted`, true));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
    errors = yield call(generateFormErrors, e);
    yield put(
      autofill(form, `errorMessage`, e.response.data["hydra:description"])
    );
  }
  yield put(stopSubmit(form, errors));
}

export function* callEditSparePartsRequest({
  payload: { url, body, form },
  type,
}: any): Generator<any, void, any> {
  let errors = {};
  yield put(startSubmit(form));
  try {
    const response = yield call(client.put, url, body);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
    errors = yield call(generateFormErrors, e);
    yield put(
      autofill(form, `errorMessage`, e.response.data["hydra:description"])
    );
  }
  yield put(stopSubmit(form, errors));
}

export default function* watchSagaForSparePartsRequest() {
  yield takeEvery(SPR_FETCH_CONTACT, callFetchContact);
  yield takeEvery(SPR_FETCH_AIRPORT, callFetchAirport);
  yield takeEvery(SPR_FETCH_DELIVERY_ADDRESSES, callFetchDeliveryAddresses);
  yield takeLatest(
    SPR_CREATE_TOC_SPARE_PARTS_REQUEST,
    callCreateTOCSparePartsRequest
  );
  yield takeLatest(
    [SPR_EDIT_TOC_SPARE_PARTS_REQUEST, SPR_EDIT_SPARE_PARTS_REQUEST_ADDRESS],
    callEditSparePartsRequest
  );
  yield takeEvery(
    SPR_CREATE_SB_SPARE_PARTS_REQUEST,
    callCreateSBSparePartsRequest
  );
}
