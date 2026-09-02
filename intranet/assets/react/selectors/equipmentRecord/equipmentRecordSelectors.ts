import { createSelector } from "reselect";
import _ from "lodash";
import moment from "moment";
import { RootState } from "../../store";

const getEquipmentRecords = (state: RootState) => {
  return state.equipmentRecord.equipmentRecords;
};

export const getEquipmentRecordsMapping = createSelector(
  [getEquipmentRecords],
  (equipmentRecords) => {
    if (!equipmentRecords) {
      return [];
    }

    return Object.values(equipmentRecords).map(
      (equipmentRecord: any, index) => {
        equipmentRecord = {
          ...equipmentRecord,
          estimatedGreenTagDate: moment(
            equipmentRecord.estimatedGreenTagDate
          ).isValid()
            ? moment(equipmentRecord.estimatedGreenTagDate).toDate()
            : null,
          firstGreenTagDate: moment(equipmentRecord.firstGreenTagDate).isValid()
            ? moment(equipmentRecord.firstGreenTagDate).toDate()
            : null,
          greenTagDate: moment(equipmentRecord.greenTagDate).isValid()
            ? equipmentRecord.greenTagDate
            : null,
          yellowTagDate: moment(equipmentRecord.yellowTagDate).isValid()
            ? moment(equipmentRecord.yellowTagDate).toDate()
            : null,
          dateShipped: moment(equipmentRecord.dateShipped).isValid()
            ? moment(equipmentRecord.dateShipped).toDate()
            : null,
          lastPreDeliveryInspection: equipmentRecord.lastPreDeliveryInspection
            ? {
                ...equipmentRecord.lastPreDeliveryInspection,
                plannedAt: moment(
                  equipmentRecord.lastPreDeliveryInspection.plannedAt
                ).isValid()
                  ? moment(
                      equipmentRecord.lastPreDeliveryInspection.plannedAt
                    ).toDate()
                  : null,
                status: equipmentRecord.lastPreDeliveryInspection.status,
              }
            : {
                plannedAt: null,
                status: "",
                equipmentRecord: equipmentRecord["@id"],
              },
          value: _.get(equipmentRecord, "@id"),
          label: `${equipmentRecord.type} / ${equipmentRecord.model} - ${equipmentRecord.serialNumber}`,
        };

        return {
          ...equipmentRecord,
          index,
        };
      }
    );
  }
);
