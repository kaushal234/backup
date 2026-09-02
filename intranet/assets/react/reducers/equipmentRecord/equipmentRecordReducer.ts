import { AnyAction, Reducer } from "redux";
import {
  SUCCESS,
  API_FETCH_ER,
  API_UPDATE_ER,
  FAILED,
  HIDE_ERROR_ALERT,
} from "../../constants";

interface IEquipmentRecordState {
  equipmentRecords: Array<any>;
  updatedLinesSuccess: Array<any>;
  showError: boolean;
  errorMessage: string;
  equipmentRecordsListIsLoading?: boolean;
}

const EQUIPMENT_RECORD_IRI = "/equipment_records/";

let initialState: IEquipmentRecordState = {
  equipmentRecords: [],
  updatedLinesSuccess: [],
  showError: false,
  errorMessage: "",
};

if (
  window.SF_INITIAL_STORE_STATE &&
  window.SF_INITIAL_STORE_STATE.equipmentRecord
) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.equipmentRecord,
  };
}

const equipmentRecordReducer: Reducer<IEquipmentRecordState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  const equipmentRecords: any = {};
  switch (action.type) {
    case API_FETCH_ER:
      return {
        ...state,
        equipmentRecordsListIsLoading: true,
      };
    case API_FETCH_ER + SUCCESS:
      action.payload.data["hydra:member"].forEach((equipmentRecord: any) => {
        equipmentRecords[equipmentRecord["@id"]] = equipmentRecord;
      });
      return {
        ...state,
        equipmentRecords: { ...state.equipmentRecords, ...equipmentRecords },
        equipmentRecordsListIsLoading: false,
      };
    case API_UPDATE_ER + SUCCESS: {
      state.equipmentRecords[action.payload.data["@id"]] = {
        ...state.equipmentRecords[action.payload.data["@id"]],
        ...action.payload.data,
      };
      const equipmentRecordIri = EQUIPMENT_RECORD_IRI + action.payload.data.id;
      state.updatedLinesSuccess.push(equipmentRecordIri);
      return {
        ...state,
        showError: false,
      };
    }
    case API_UPDATE_ER + FAILED:
      return {
        ...state,
        showError: true,
        errorMessage: action.payload.data["hydra:description"],
      };

    case `ODP_${HIDE_ERROR_ALERT}`:
      state.showError = false;
      return {
        ...state,
      };
    default:
      break;
  }
  return state;
};

export default equipmentRecordReducer;
