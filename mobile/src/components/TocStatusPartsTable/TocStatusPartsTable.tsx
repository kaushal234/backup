import React, { useEffect, useState } from "react";
import { ITocParts } from "../../@type/ITocParts";
import SimpleTable from "../SimpleTable/SimpleTable";
import FormSwitchWithHook from "../FormSwitchWithHook/FormSwitchWithHook";
import { ITocDefectivePart } from "../../@type/ITocDefectivePart";
import "./TocStatusPartsTable.css";
import FormLabel from "../FormLabel/FormLabel";

interface ITocPart extends ITocDefectivePart {
  vendorPartNumber: string;
  defective: boolean;
}

interface IProps {
  data: ITocParts;
  dataCy: string;
  setDefectiveParts: (parts: Array<ITocDefectivePart>) => void;
}

export default function TocStatusPartsTable(props: IProps) {
  const { data, dataCy, setDefectiveParts } = props;
  const [parts, setParts] = useState<Array<ITocPart>>([]);

  const handleDefectiveChange = (idx: number, newValue: boolean) => {
    setParts((prevValue) => {
      const result = [...prevValue];
      result[idx].defective = newValue;
      return result;
    });
  };

  useEffect(() => {
    const newParts: Array<ITocPart> = [];
    data.parts.forEach((part) => {
      newParts.push({
        partNumber: part.partNumber ?? "",
        description: part.description,
        quantity: part.quantity,
        vendorPartNumber: part.vendorPartNumber ?? "",
        defective: false,
      });
    });
    data.sparePartsRequests.forEach((spr) => {
      spr.parts.forEach((part) => {
        newParts.push({
          partNumber: part.partNumber,
          description: part.description,
          quantity: part.quantity,
          vendorPartNumber: "",
          defective: false,
        });
      });
    });
    setParts(newParts);
  }, [data]);

  useEffect(() => {
    const newDefectiveParts: Array<ITocDefectivePart> = [];
    parts.forEach((part) => {
      if (part.defective) {
        newDefectiveParts.push({
          partNumber: part.partNumber,
          description: part.description,
          quantity: part.quantity,
        });
      }
    });
    setDefectiveParts(newDefectiveParts);
  }, [JSON.stringify(parts)]);

  return (
    <div>
      <FormLabel label="Parts" />
      <div className="toc_status_parts_table__wrapper">
        <SimpleTable
          dataCy={`${dataCy}-table`}
          isOneLiner
          wrapHeader
          noCard
          headers={[
            { value: "toc_parts.table.defective" },
            { value: "toc_parts.table.part_number" },
            { value: "toc_parts.table.vendor_part_number" },
            { value: "toc_parts.table.description", minWidth: "300px" },
          ]}
          rows={parts.map((part, idx) => [
            <div className="manual_document_group_table__actions">
              <FormSwitchWithHook
                defaultValue={part.defective}
                onChange={(newValue) => handleDefectiveChange(idx, newValue)}
                dataCy={`${dataCy}-table-defective-${idx}`}
              />
            </div>,
            part.partNumber,
            part.vendorPartNumber,
            part.description,
          ])}
          noContentMessage="toc_parts.no_content"
        />
      </div>
    </div>
  );
}
