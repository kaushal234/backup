import { call, put, takeLatest } from "redux-saga/effects";
import { AxiosError } from "axios";
import { client } from "../../../store";
import { APICallFailed, APICallSuccess } from "../../../actions/genericActions";
import {
  CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
  UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
} from "../../../constants";
import { ISupplierCorrectiveActionRequestAction } from "../../../types/ISupplierCorrectiveActionRequestAction";
import { ISupplierCorrectiveActionRequestActionResponse } from "../../../types/ISupplierCorrectiveActionRequestActionResponse";

interface callCreateSupplierCorrectiveActionRequestProps {
  payload: {
    url: string;
    body: ISupplierCorrectiveActionRequestAction;
  };
  type: string;
}

export function* callCreateSupplierCorrectiveActionRequest({
  payload: { url, body },
  type,
}: callCreateSupplierCorrectiveActionRequestProps) {
  try {
    const response: ISupplierCorrectiveActionRequestActionResponse = yield call(
      client.post,
      url,
      body
    );
    yield put(APICallSuccess(type, response));
  } catch (error: unknown) {
    const axiosError = error as AxiosError;
    yield put(APICallFailed(type, axiosError.response));
  }
}
export function* callUpdateSupplierCorrectiveActionRequest({
  payload: { url, body },
  type,
}: callCreateSupplierCorrectiveActionRequestProps) {
  try {
    const response: ISupplierCorrectiveActionRequestActionResponse = yield call(
      client.put,
      url,
      body
    );
    yield put(APICallSuccess(type, response));
  } catch (error: unknown) {
    const axiosError = error as AxiosError;
    yield put(APICallFailed(type, axiosError.response));
  }
}

export default function* watchSagaForTask() {
  yield takeLatest(
    CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
    callCreateSupplierCorrectiveActionRequest
  );
  yield takeLatest(
    UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
    callUpdateSupplierCorrectiveActionRequest
  );
}
