import {
  WRITE_PRE_DELIVERY_INSPECTION,
  PRE_DELIVERY_INSPECTION_STATUS_UPDATE,
} from "../../constants";

function buildPayload(preDeliveryInspection: any, equipmentRecordIri: any) {
  let apiMethod = "POST";
  let url = "/pre_delivery_inspections";
  // We want to Edit existing PDI if there is an Id and if the PDI is not in a closed Status
  if (
    preDeliveryInspection["@id"] &&
    preDeliveryInspection.status === "SCHEDULED"
  ) {
    apiMethod = "PUT";
    url = preDeliveryInspection["@id"];
  }
  return {
    request: {
      apiMethod,
      url,
      body: {
        equipmentRecord: equipmentRecordIri,
        plannedAt: preDeliveryInspection.plannedAt,
      },
    },
  };
}
export function writePreDeliveryInspection(
  preDeliveryInspection: any,
  equipmentRecordIri: any,
  index: any,
  type = WRITE_PRE_DELIVERY_INSPECTION
) {
  return {
    type,
    payload: buildPayload(preDeliveryInspection, equipmentRecordIri),
    index,
  };
}

export function updatePreDeliveryInspectionStatus(
  preDeliveryInspection: any,
  index: any,
  type = PRE_DELIVERY_INSPECTION_STATUS_UPDATE
) {
  return {
    type,
    payload: {
      request: {
        url: `${preDeliveryInspection["@id"]}/status`,
        body: { status: preDeliveryInspection.status },
      },
    },
    index,
  };
}
