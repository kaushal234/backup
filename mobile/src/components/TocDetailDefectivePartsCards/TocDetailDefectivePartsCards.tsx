import React from "react";
import { ITechnicianOnCall } from "../../@type/IGetTechnicalOnCallsResponse";
import InfoCard from "../InfoCard/InfoCard";
import "./TocDetailDefectivePartsCards.css";

interface IProps {
  data: ITechnicianOnCall;
}

export default function TocDetailDefectivePartsCards(props: IProps) {
  const { data } = props;

  return (
    <div className="toc_detail_defective_parts_cards__wrapper">
      {data.defectiveParts.map((part, idx) => (
        <InfoCard
          data={[
            {
              title: "toc_parts.defective_parts_table.part_number",
              value: part.partNumber || "---",
            },
            {
              title: "toc_parts.defective_parts_table.description",
              value: part.description || "---",
            },
            {
              title: "toc_parts.defective_parts_table.quantity",
              value: part.quantity,
            },
          ]}
          dataCy={`toc-defective-parts-${idx}`}
        />
      ))}
    </div>
  );
}
