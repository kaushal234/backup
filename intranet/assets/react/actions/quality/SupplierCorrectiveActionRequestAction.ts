import {
  CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
  UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
} from "../../constants";
import { ISupplierCorrectiveActionRequestAction } from "../../types/ISupplierCorrectiveActionRequestAction";

export function createSupplierCorrectiveActionRequest(
  values: ISupplierCorrectiveActionRequestAction
) {
  return {
    type: CREATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
    payload: {
      url: "/quality/supplier_corrective_action_requests",
      body: values,
    },
  };
}

export function updateSupplierCorrectiveActionRequest(
  values: ISupplierCorrectiveActionRequestAction
) {
  return {
    type: UPDATE_SUPPLIER_CORRECTIVE_ACTION_REQUEST,
    payload: {
      url: `/quality/supplier_corrective_action_requests/${values.id}`,
      body: values,
    },
  };
}
