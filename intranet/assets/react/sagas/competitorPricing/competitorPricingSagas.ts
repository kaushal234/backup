import { autofill } from "redux-form";
import { put, call, takeEvery } from "redux-saga/effects";
import { client } from "../../store";
import { APICallFailed, APICallSuccess } from "../../actions/genericActions";
import { CPR_WRITE_COMPETITOR_PRICING } from "../../constants";

export function* callWriteCompetitorPricing({
  payload: { url, body, form },
  type,
}: any): Generator<any, void, any> {
  try {
    const response = yield call(client.post, url, body);
    yield put(APICallSuccess(type, response));
  } catch (e: any) {
    yield put(APICallFailed(type, e.response));
    yield put(
      autofill(
        form,
        `competitorPricing.errorMessage`,
        e.response.data["hydra:description"]
      )
    );
  }
}

export default function* watchSagaForCompetitorPricings() {
  yield takeEvery(CPR_WRITE_COMPETITOR_PRICING, callWriteCompetitorPricing);
}
