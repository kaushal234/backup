import { AnyAction } from "redux";
import { Reducer } from "@reduxjs/toolkit";
import { COMMON_SET_LOADING } from "../../constants";

interface ILoaderState {
  isLoading: any;
}

const initialState: ILoaderState = {
  isLoading: false,
};

const loaderReducer: Reducer<ILoaderState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case COMMON_SET_LOADING:
      return {
        ...state,
        isLoading: action.payload.isLoading,
      };
    default:
      break;
  }
  return state;
};

export default loaderReducer;
