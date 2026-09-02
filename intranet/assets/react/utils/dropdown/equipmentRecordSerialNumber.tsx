import React from "react";
import { IDropdownItem, IEquipmentRecord } from "../../types/IDropdownItem";
import {
  getEquipmentRecordAutocomplete,
  IEquipmentRecordAutocompleteItem,
} from "../../api/getEquipmentRecordAutocomplete";

export const createEquipmentRecordSerialNoDropdownItem = (item: {
  "@id": string;
  serialNumber: string;
  equipmentRecord?: IEquipmentRecord;
  type?: string | null;
  model?: string | null;
  customerSerialNumber?: string | null;
}): IDropdownItem => {
  const additionalInfo = item.equipmentRecord
    ? {
        type: "EquipmentRecord" as const,
        data: item.equipmentRecord,
      }
    : undefined;

  const left = [item.type, item.model].filter(Boolean).join(" / ");
  const right = [item.serialNumber, item.customerSerialNumber]
    .filter(Boolean)
    .join(" / ");
  const label = [left, right].filter(Boolean).join(" - ");

  return {
    value: item["@id"],
    label,
    data: additionalInfo,
  };
};

export const fetchEquipmentRecordSerialNo = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getEquipmentRecordAutocomplete(value);
  return (response.data?.["hydra:member"] ?? []).map(
    (item: IEquipmentRecordAutocompleteItem) =>
      createEquipmentRecordSerialNoDropdownItem({
        ...item,
        equipmentRecord: item,
      })
  );
};

function highlightMatch(label: string, inputValue: string): React.ReactNode {
  if (!inputValue) return label;
  const escaped = inputValue.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  const parts = label.split(new RegExp(`(${escaped})`, "gi"));
  let offset = 0;
  return (
    <>
      {parts.map((part, i) => {
        const key = offset;
        offset += part.length;
        return i % 2 === 1 ? (
          <mark key={key} style={{ padding: 0 }}>
            {part}
          </mark>
        ) : (
          part
        );
      })}
    </>
  );
}

export function formatEquipmentRecordOptionLabel(
  option: { label: string },
  { context, inputValue }: { context: string; inputValue: string }
): React.ReactNode {
  if (context === "value") return option.label;
  return highlightMatch(option.label, inputValue);
}
