import { AnyAction, Reducer } from "redux";
import { SEARCH_SET_FILTER_TEXT } from "../../constants";

interface ISearchState {
  filterText: string;
}

const initialState: ISearchState = {
  filterText: "",
};

const searchReducer: Reducer<ISearchState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case SEARCH_SET_FILTER_TEXT:
      state = {
        ...state,
        filterText: action.payload.filterText,
      };
      break;
    default:
      break;
  }
  return state;
};

export default searchReducer;
