import { AnyAction, Reducer } from "redux";
import { DMS_FETCH_DMS, SUCCESS } from "../../constants";

interface IDmsState {
  dms: Array<any>;
  dmsListIsLoading?: boolean;
}

const initialState: IDmsState = {
  dms: [],
};

const dmsReducer: Reducer<IDmsState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case DMS_FETCH_DMS + SUCCESS: {
      const dms: any = {};
      action.payload.data["hydra:member"].forEach((singleDMS: any) => {
        dms[singleDMS["@id"]] = singleDMS;
      });
      return {
        ...state,
        dms,
        dmsListIsLoading: false,
      };
    }
    case DMS_FETCH_DMS:
      return {
        ...state,
        dmsListIsLoading: true,
      };
    default:
      break;
  }
  return state;
};

export default dmsReducer;
