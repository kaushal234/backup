import { AnyAction, Reducer } from "redux";
import {
  COMPETITORS_FETCH_COMPETITOR,
  COMPETITORS_FETCH_COMPETITORS,
  SUCCESS,
} from "../../constants";

interface ICompetitorState {
  competitors: Array<any>;
  competitorsListIsLoading?: boolean;
}

const initialState: ICompetitorState = {
  competitors: [],
};

const competitorReducer: Reducer<ICompetitorState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  const competitors: any = {};
  switch (action.type) {
    case COMPETITORS_FETCH_COMPETITORS:
      return {
        ...state,
        competitorsListIsLoading: true,
      };
    case COMPETITORS_FETCH_COMPETITORS + SUCCESS:
      action.payload.data["hydra:member"].forEach((competitor: any) => {
        competitors[competitor["@id"]] = competitor;
      });
      return {
        ...state,
        competitors,
        competitorsListIsLoading: false,
      };
    case COMPETITORS_FETCH_COMPETITOR + SUCCESS:
      return {
        ...state,
        competitors: {
          ...competitors,
          [action.payload.data["@id"]]: action.payload.data,
        },
      };
    default:
      break;
  }
  return state;
};

export default competitorReducer;
