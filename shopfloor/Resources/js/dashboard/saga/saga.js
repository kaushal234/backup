import { call, put, takeLatest } from 'redux-saga/effects'
import { requestSuccess,requestFailed, } from "../action/actions"
import { client } from "../store/store"
import  queryString  from 'query-string'


export function * callReport ({ payload: { request: { url, body } }, type }) {
    try {
        //let response = yield call(client.get, url)
        const response = yield call(client.post, url, queryString.stringify(body))
        yield put(requestSuccess(type, response.data))
    } catch (e) {
        yield put(requestFailed(type,e.response))
    }
}

export default function * watchSagaForReport () {
    yield takeLatest("FILL_REPORT", callReport)
}