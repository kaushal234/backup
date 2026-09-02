import { call, put, takeLatest } from "redux-saga/effects";
import { AxiosError, AxiosResponse } from "axios";
import { client } from "../../store";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";
import { LOCATION_FETCH_FACTORIES } from "../../constants";

interface FetchFactoriesProps {
  url: string;
  type: string;
}

export function* callFetchFactories({ url, type }: FetchFactoriesProps) {
  try {
    const response: AxiosResponse<never> = yield call(client.get, url);
    yield put(APICallSuccess(type, response));
  } catch (error: unknown) {
    const axiosError = error as AxiosError;
    yield put(APICallFailed(type, axiosError.response?.data));
  }
}

export default function* watchSagaForLocation() {
  yield takeLatest(LOCATION_FETCH_FACTORIES, callFetchFactories);
}
