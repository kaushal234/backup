import { AnyAction, Reducer } from "redux";
import {
  FAILED,
  SALES_CLEAR_CUSTOMERS,
  SALES_CREATE_CUSTOMER_FORM,
  SALES_FETCH_CUSTOMER,
  SALES_FETCH_CUSTOMERS,
  SUCCESS,
} from "../../constants";

interface ICustomerState {
  customers: Array<any>;
  customersListIsLoading?: boolean;
  customerNotFound?: boolean;
  showSuccess?: boolean;
  showError?: boolean;
  inputName?: any;
}

let initialState: ICustomerState = {
  customers: [],
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.customer) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.customer,
  };
}

const customerReducer: Reducer<ICustomerState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  const customers: any = [];
  switch (action.type) {
    case SALES_FETCH_CUSTOMERS:
      return {
        ...state,
        customersListIsLoading: true,
        customerNotFound: false,
        showSuccess: false,
        showError: false,
      };
    case SALES_FETCH_CUSTOMERS + SUCCESS:
      if (action.payload.data["hydra:member"].length < 1) {
        return {
          ...state,
          customers,
          customersListIsLoading: false,
          customerNotFound: true,
          showSuccess: false,
          showError: false,
          inputName: action.payload.name,
        };
      }
      action.payload.data["hydra:member"].forEach((customer: any) => {
        customers[customer["@id"]] = customer;
      });
      return {
        ...state,
        customers: { ...customers },
        customersListIsLoading: false,
        customerNotFound: false,
        showSuccess: false,
        showError: false,
      };
    case SALES_FETCH_CUSTOMER + SUCCESS:
      return {
        ...state,
        customers: {
          ...customers,
          [action.payload.data["@id"]]: action.payload.data,
        },
      };
    case SALES_CREATE_CUSTOMER_FORM + SUCCESS:
      return {
        ...state,
        customerNotFound: false,
        showSuccess: true,
        showError: false,
      };
    case SALES_CREATE_CUSTOMER_FORM + FAILED:
      return {
        ...state,
        customerNotFound: false,
        showSuccess: false,
        showError: true,
      };
    case SALES_CLEAR_CUSTOMERS:
      return {
        ...state,
        customers,
      };
    default:
      break;
  }
  return state;
};

export default customerReducer;
