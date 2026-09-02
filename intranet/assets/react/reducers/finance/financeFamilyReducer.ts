import { AnyAction, Reducer } from "redux";
import {
  SUCCESS,
  FAILED,
  FINANCE_FETCH_FINANCE_FAMILIES,
  FINANCE_HANDLE_FINANCE_FAMILY_FACTORIES,
  FINANCE_REMOVE_PRICING,
} from "../../constants";

export interface IFinanceFamilyState {
  financeFamilies: Array<any>;
}

let initialState: IFinanceFamilyState = {
  financeFamilies: [],
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.finance) {
  initialState = { ...initialState, ...window.SF_INITIAL_STORE_STATE.finance };
}

const financeFamilyReducer: Reducer<IFinanceFamilyState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case FINANCE_FETCH_FINANCE_FAMILIES + SUCCESS:
      return {
        ...state,
        financeFamilies: action.payload.data["hydra:member"],
      };
    case FINANCE_FETCH_FINANCE_FAMILIES + FAILED:
      return {
        ...state,
        financeFamilies: [],
      };
    case FINANCE_HANDLE_FINANCE_FAMILY_FACTORIES:
      if (action.value === true) {
        action.financeFamily.factories.push(action.factory.value);
      } else {
        ["averagePrice", "averageMargin", "sso", "factory"].forEach((key) => {
          if (
            Object.hasOwn(
              action.financeFamily.pricings[
                `${action.sso}-${action.factory.value}`
              ],
              key
            )
          ) {
            action.financeFamily.pricings[
              `${action.sso}-${action.factory.value}`
            ][key] = "";
          }
        });
        action.financeFamily.factories = action.financeFamily.factories.filter(
          (item: any) => {
            return item !== action.factory.value;
          }
        );
      }
      state.financeFamilies[action.index] = action.financeFamily;
      return {
        ...state,
      };
    case FINANCE_REMOVE_PRICING: {
      const keys = ["averagePrice", "averageMargin", "sso", "factory"];
      keys.forEach((key) => {
        if (
          Object.hasOwn(
            action.financeFamily.pricings[`${action.sso}-${action.factory}`],
            key
          )
        ) {
          action.financeFamily.pricings[`${action.sso}-${action.factory}`][
            key
          ] = "";
        }
      });
      state.financeFamilies[action.index] = action.financeFamily;
      return {
        ...state,
      };
    }
    default:
      break;
  }
  return state;
};

export default financeFamilyReducer;
