import { call, put } from "redux-saga/effects";
import { fetchAPI } from "../../utils/api";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";

export default function* callGenericGetGenerator({
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
