import { createStore, combineReducers, applyMiddleware } from 'redux'
import { reducer as formReducer } from 'redux-form'
import axios from 'axios'
import createSagaMiddleware from 'redux-saga'
import { all } from 'redux-saga/effects'
import {factories, families, reportSearch} from '../reducer/reducer'
import saga from '../saga/saga'

//axios
const httpClientConfig = {
    baseURL: process.env.NODE_ENV === 'development' ? 'http://localhost/shop/autoselect.php' : '/shop/autoselect.php',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded',
    }
}
const client = axios.create(httpClientConfig)
export { httpClientConfig, client }

//saga
const sagaMiddleware = createSagaMiddleware()

//combine reducer
const rootReducer = combineReducers({
    form: formReducer,
    factories,
    families,
    reportSearch
})

const middlewares = [
    sagaMiddleware
]

let enhancer = applyMiddleware(...middlewares)

if (process.env.NODE_ENV === 'development') {

    const composeEnhancers = typeof window === 'object' && window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ ? window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({ }) : compose
    enhancer = composeEnhancers(applyMiddleware(...middlewares))
}

//create store
export const store = createStore(
    rootReducer, /* preloadedState, */
    enhancer
);

function * root () {
    yield all([
        saga()
    ])
}

sagaMiddleware.run(root)

// export const store = createStore(rootReducer, enhancer)