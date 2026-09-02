import { Reducer, AnyAction } from "redux";
import {
  CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
  FAILED,
  SUCCESS,
  UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
} from "../../../constants";

interface ISupplierCorrectiveActionRequestState {
  showSuccess: boolean;
  showError: boolean;
  showLoading: boolean;
  showFiles: boolean;
  id?: number | null;
  errorMessage: string;
}

const initialState = {
  showSuccess: false,
  showError: false,
  showLoading: false,
  showFiles: false,
  id: null,
  errorMessage: "",
};
const supplierCorrectiveActionRequestReducer: Reducer<
  ISupplierCorrectiveActionRequestState,
  AnyAction
> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST:
    case UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST:
      return {
        ...state,
        showLoading: true,
      };
    case CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST + SUCCESS:
    case UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST + SUCCESS:
      return {
        ...state,
        showLoading: false,
        showSuccess: true,
        id: action.payload.data.id,
      };
    case CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST + FAILED:
    case UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST + FAILED:
      return {
        ...state,
        showLoading: false,
        showError: true,
        errorMessage: action.payload.data["hydra:description"],
      };
    default:
      return {
        ...state,
      };
  }
};
export default supplierCorrectiveActionRequestReducer;
