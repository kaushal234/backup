import { AnyAction, Reducer } from "redux";
import { SUCCESS, DIRECTORY_FETCH_BUSINESS_UNITS } from "../../constants";

interface IBusinessUnitState {
  businessUnits: Array<any>;
}

let initialState: IBusinessUnitState = {
  businessUnits: [],
};

if (
  window.SF_INITIAL_STORE_STATE &&
  window.SF_INITIAL_STORE_STATE.businessUnit
) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.businessUnit,
  };
}

const businessUnitReducer: Reducer<IBusinessUnitState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  const businessUnits: any = {};
  switch (action.type) {
    case DIRECTORY_FETCH_BUSINESS_UNITS:
      return {
        ...state,
      };
    case DIRECTORY_FETCH_BUSINESS_UNITS + SUCCESS:
      action.payload.data["hydra:member"].forEach((businessUnit: any) => {
        businessUnits[businessUnit["@id"]] = businessUnit;
      });
      return {
        ...state,
        businessUnits,
      };
    default:
      break;
  }
  return state;
};

export default businessUnitReducer;
