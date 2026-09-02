import { AnyAction, Reducer } from "redux";
import {
  ACTIVITY_CREATE_COMMENT,
  ACTIVITY_FETCH_COMMENTS,
  ACTIVITY_FETCH_LOGS,
  SUCCESS,
} from "../../constants";

interface IActivityState {
  logs: any;
  comments: any;
  pendingCommentCreations: any;
}

const initialState: IActivityState = {
  logs: {},
  comments: {},
  pendingCommentCreations: {},
};

const activityReducer: Reducer<IActivityState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  const comments = !state.comments ? {} : state.comments;
  switch (action.type) {
    case ACTIVITY_FETCH_LOGS + SUCCESS: {
      const logs = !state.logs ? {} : state.logs;
      return {
        ...state,
        logs: {
          ...logs,
          [action.payload.iri]: action.payload.data["hydra:member"],
        },
      };
    }
    case ACTIVITY_FETCH_COMMENTS:
      return {
        ...state,
      };
    case ACTIVITY_FETCH_COMMENTS + SUCCESS:
      return {
        ...state,
        comments: {
          ...comments,
          [action.payload.iri]: action.payload.data["hydra:member"],
        },
      };
    case ACTIVITY_CREATE_COMMENT:
      return {
        ...state,
        pendingCommentCreations: {
          ...state.pendingCommentCreations,
          [action.payload.request.body.resource]: true,
        },
      };
    case ACTIVITY_CREATE_COMMENT + SUCCESS: {
      const resourceIri = action.payload.iri;

      const newComments = [...(comments[resourceIri] || [])];

      newComments.push({
        ...action.payload.data,
        position:
          resourceIri in state.comments
            ? state.comments[resourceIri].length + 1
            : 1,
      });

      const pendingCommentCreations = { ...state.pendingCommentCreations };

      delete pendingCommentCreations[resourceIri];

      return {
        ...state,
        pendingCommentCreations,
        comments: {
          ...state.comments,
          [resourceIri]: newComments,
        },
      };
    }
    default:
      break;
  }
  return state;
};

export default activityReducer;
