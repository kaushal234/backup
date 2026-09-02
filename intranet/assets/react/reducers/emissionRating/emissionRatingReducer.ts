import { AnyAction, Reducer } from "redux";

interface IEmissionRatingState {
  emissionRating: Array<any>;
  emissionRatings?: any;
}

let initialState: IEmissionRatingState = {
  emissionRating: [],
};

if (
  window.SF_INITIAL_STORE_STATE &&
  window.SF_INITIAL_STORE_STATE.emissionRating
) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.emissionRating,
  };
}

const emissionRatingReducer: Reducer<IEmissionRatingState, AnyAction> = (
  state = initialState
) => {
  return state;
};

export default emissionRatingReducer;
