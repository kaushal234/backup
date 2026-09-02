import React from "react";
import OnTimeDeliveryLine from "./OnTimeDeliveryLine";

interface IProps {
  fields: any;
  grid: any;
}

const EquipmentRecordsArray = (props: IProps) => {
  const { fields, grid } = props;
  return fields.map((equipmentRecord: any, index: number) => {
    return (
      <OnTimeDeliveryLine
        key={index}
        index={index}
        equipmentRecordPath={equipmentRecord}
        equipmentRecord={fields.get(index)}
        grid={grid}
      />
    );
  });
};

export default EquipmentRecordsArray;
