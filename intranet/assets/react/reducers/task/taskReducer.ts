import { AnyAction, Reducer } from "redux";
import { FAILED, CREATE_TASK, SUCCESS, EDIT_TASK } from "../../constants";

interface ITaskState {
  showSuccess: boolean;
  showError: boolean;
  showLoading: boolean;
  errorMessage: string;
}

const initialState: ITaskState = {
  showSuccess: false,
  showError: false,
  showLoading: false,
  errorMessage: "",
};

const taskReducer: Reducer<ITaskState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case CREATE_TASK:
    case EDIT_TASK:
      return {
        ...state,
        showLoading: true,
        showSuccess: false,
        showError: false,
      };
    case CREATE_TASK + SUCCESS:
    case EDIT_TASK + SUCCESS:
      return {
        ...state,
        showLoading: false,
        showSuccess: true,
        showError: false,
      };
    case CREATE_TASK + FAILED:
    case EDIT_TASK + FAILED:
      return {
        ...state,
        showError: true,
        showSuccess: false,
        showLoading: false,
        errorMessage: action.payload.data["hydra:description"] ?? "",
      };
    default:
      break;
  }
  return state;
};

export default taskReducer;
