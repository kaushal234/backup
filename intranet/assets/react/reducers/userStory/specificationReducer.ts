import { AnyAction, Reducer } from "redux";
import {
  FAILED,
  HIDE_SUCCESS_ALERT,
  SUCCESS,
  SPECIFICATION_GET,
  USER_STORY_EDIT,
  USER_STORY_GET,
  USER_STORY_DELETE,
  USER_STORY_UPDATE_STATUS,
  USER_STORY_WRITE,
  USER_STORY_RESET,
  HIDE_ERROR_ALERT,
  USER_STORY_DUPLICATE,
} from "../../constants";

interface ISpecificationState {
  showLoading: boolean;
  showSuccess: boolean;
  showFailed: boolean;
  errorMessage: string;
  showUserStoryDetail: boolean;
  loadingUserStory: boolean;
  userStoryRefresh: boolean;
  specificationStatus: string;
  showList: boolean;
  userStories: any;
  userStory: any;
  loadUserStory?: any;
}

let initialState: ISpecificationState = {
  showLoading: true,
  showSuccess: false,
  showFailed: false,
  errorMessage: "",
  showUserStoryDetail: false,
  loadingUserStory: false,
  userStoryRefresh: false,
  specificationStatus: "",
  showList: false,
  userStories: {},
  userStory: {},
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.userStory) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.userStory,
  };
}

const specificationReducer: Reducer<ISpecificationState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case USER_STORY_GET + FAILED:
    case USER_STORY_DELETE + FAILED:
    case USER_STORY_DUPLICATE + FAILED:
      return {
        ...state,
        showFailed: true,
        errorMessage: action.payload.data["hydra:description"] ?? "",
        userStoryRefresh: true,
        showList: true,
      };
    case USER_STORY_WRITE + FAILED:
    case USER_STORY_EDIT + FAILED:
      return {
        ...state,
        showFailed: true,
        errorMessage: action.payload.data["hydra:description"] ?? "",
      };
    case USER_STORY_WRITE + SUCCESS:
    case USER_STORY_EDIT + SUCCESS:
      return {
        ...state,
        showSuccess: true,
        userStoryRefresh: true,
        showList: true,
        userStory: action.payload.data,
        showUserStoryDetail: true,
      };
    case SPECIFICATION_GET + SUCCESS:
      return {
        ...state,
        specificationStatus: action.payload.data.status,
        userStories: action.payload.data.userStories,
        showLoading: false,
      };
    case USER_STORY_GET:
      return {
        ...state,
        loadingUserStory: true,
      };
    case USER_STORY_GET + SUCCESS:
      return {
        ...state,
        userStory: action.payload.data,
        showUserStoryDetail: true,
        loadingUserStory: false,
      };
    case USER_STORY_DELETE + SUCCESS:
      return {
        ...state,
        showUserStoryDetail: false,
        userStoryRefresh: true,
      };
    case USER_STORY_UPDATE_STATUS + SUCCESS:
      return {
        ...state,
        userStory: action.payload.data,
        userStoryRefresh: true,
      };
    case USER_STORY_RESET: {
      return {
        ...state,
        userStoryRefresh: false,
      };
    }
    case USER_STORY_DUPLICATE + SUCCESS: {
      return {
        ...state,
        userStoryRefresh: true,
      };
    }
    case `USER_STORY_${HIDE_SUCCESS_ALERT}`:
      return {
        ...state,
        showSuccess: false,
      };
    case `USER_STORY_${HIDE_ERROR_ALERT}`:
      return {
        ...state,
        showFailed: false,
      };
    default:
      return state;
  }
};

export default specificationReducer;
