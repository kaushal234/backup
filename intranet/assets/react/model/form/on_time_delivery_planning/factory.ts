import _ from "lodash";
import moment from "moment";

const onTimeDeliveryEquipmentRecordFactory = (
  value: any,
  equipmentRecordBeforeChanges: any
) => {
  const updatedEquipmentRecord: any = {
    "@id": _.get(value, "@id", null),
    id: value.id,
    estimatedGreenTagDate: moment(value.estimatedGreenTagDate).isValid()
      ? moment(value.estimatedGreenTagDate).format("YYYY-MM-DDTHH:mm:ssZ")
      : null,
    firstGreenTagDate: moment(value.firstGreenTagDate).isValid()
      ? moment(value.firstGreenTagDate).format("YYYY-MM-DDTHH:mm:ssZ")
      : null,
    greenTagDate: moment(value.greenTagDate).isValid()
      ? moment(value.greenTagDate).format("YYYY-MM-DDTHH:mm:ssZ")
      : null,
    yellowTagDate: moment(value.yellowTagDate).isValid()
      ? moment(value.yellowTagDate).format()
      : null,
    dateShipped: moment(value.dateShipped).isValid()
      ? moment(value.dateShipped).format("YYYY-MM-DDTHH:mm:ssZ")
      : null,
    odpComment: value.odpComment,
  };

  if (value.greenTagDate === true) {
    updatedEquipmentRecord.greenTagDate = moment().format(
      "YYYY-MM-DDTHH:mm:ssZ"
    );
  }

  if (
    updatedEquipmentRecord.firstGreenTagDate === null &&
    moment(updatedEquipmentRecord.greenTagDate).isValid()
  ) {
    updatedEquipmentRecord.firstGreenTagDate = moment(
      updatedEquipmentRecord.greenTagDate
    ).format("YYYY-MM-DDTHH:mm:ssZ");
  }

  if (
    moment(updatedEquipmentRecord.greenTagDate).isValid() &&
    moment(updatedEquipmentRecord.yellowTagDate).isValid() &&
    moment(updatedEquipmentRecord.yellowTagDate).unix() >
      moment(updatedEquipmentRecord.greenTagDate).unix()
  ) {
    updatedEquipmentRecord.greenTagDate = null;
  }

  if (
    moment(updatedEquipmentRecord.estimatedGreenTagDate).unix() ===
    moment(equipmentRecordBeforeChanges.estimatedGreenTagDate).unix()
  ) {
    delete updatedEquipmentRecord.estimatedGreenTagDate;
  }

  if (value.greenTagDate !== true) {
    delete updatedEquipmentRecord.greenTagDate;
  }

  if (
    moment(updatedEquipmentRecord.yellowTagDate).unix() ===
    moment(equipmentRecordBeforeChanges.yellowTagDate).unix()
  ) {
    delete updatedEquipmentRecord.yellowTagDate;
  }

  if (
    updatedEquipmentRecord.dateShipped ===
      equipmentRecordBeforeChanges.dateShipped ||
    moment(updatedEquipmentRecord.dateShipped).unix() ===
      moment(equipmentRecordBeforeChanges.dateShipped).unix()
  ) {
    delete updatedEquipmentRecord.dateShipped;
  }

  if (
    moment(updatedEquipmentRecord.firstGreenTagDate).unix() ===
    moment(equipmentRecordBeforeChanges.firstGreenTagDate).unix()
  ) {
    delete updatedEquipmentRecord.firstGreenTagDate;
  }

  if (
    updatedEquipmentRecord.odpComment ===
    equipmentRecordBeforeChanges.odpComment
  ) {
    delete updatedEquipmentRecord.odpComment;
  }

  return updatedEquipmentRecord;
};

const onTimeDeliveryPreDeliveryInspectionFactory = (
  value: any,
  preDeliveryInspectionBeforeChanges: any,
  equipmentRecordIri: any
) => {
  if (value === preDeliveryInspectionBeforeChanges) {
    return value;
  }
  const updatedPreDeliveryInspection = {
    ...value,
    equipmentRecord: equipmentRecordIri,
    plannedAt: moment(value.plannedAt).isValid()
      ? moment(value.plannedAt).format("YYYY-MM-DDTHH:mm:ssZ")
      : null,
  };

  if (
    moment(updatedPreDeliveryInspection.plannedAt).unix() ===
    moment(preDeliveryInspectionBeforeChanges.plannedAt).unix()
  ) {
    delete updatedPreDeliveryInspection.plannedAt;
  }

  return updatedPreDeliveryInspection;
};

export {
  onTimeDeliveryEquipmentRecordFactory,
  onTimeDeliveryPreDeliveryInspectionFactory,
};
