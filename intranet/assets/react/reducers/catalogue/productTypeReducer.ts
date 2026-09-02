import { AnyAction, Reducer } from "redux";

interface IProductTypeState {
  productTypes: Array<any>;
}

let initialState: IProductTypeState = {
  productTypes: [],
};

if (
  window.SF_INITIAL_STORE_STATE &&
  window.SF_INITIAL_STORE_STATE.productType
) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.productType,
  };
}

const productTypeReducer: Reducer<IProductTypeState, AnyAction> = (
  state = initialState
) => {
  return state;
};

export default productTypeReducer;
