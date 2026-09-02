import { IDropdownItem, IEquipmentRecord } from "../../@type/IDropdownItem";
import { getAllEquipmentRecord } from "../../api/getAllEquipmentRecord";

export const createEquipmentRecordSerialNoDropdownItem = (item: {
  "@id": string;
  serialNumber: string;
  equipmentRecord?: IEquipmentRecord;
}): IDropdownItem => {
  const additionalInfo = item.equipmentRecord
    ? {
        type: "EquipmentRecord" as const,
        data: item.equipmentRecord,
      }
    : undefined;
  return {
    id: item["@id"],
    text: item.serialNumber,
    additionalInfo,
  };
};

export const fetchEquipmentRecordSerialNo = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllEquipmentRecord({ serialNumber: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createEquipmentRecordSerialNoDropdownItem({
      ...item,
      equipmentRecord: item,
    })
  );
};
