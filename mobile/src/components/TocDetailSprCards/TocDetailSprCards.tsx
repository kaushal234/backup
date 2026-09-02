import React, { useEffect, useState } from "react";
import { ITechnicianOnCall } from "../../@type/IGetTechnicalOnCallsResponse";
import InfoCard from "../InfoCard/InfoCard";
import "./TocDetailSprCards.css";
import SprTrackerLink from "../SprTrackerLink/SprTrackerLink";

interface IProps {
  data: ITechnicianOnCall;
  showDeletedSprOnly?: boolean;
}

export default function TocDetailSprCards(props: IProps) {
  const { data, showDeletedSprOnly } = props;

  const [rows, setRows] = useState<Array<Array<React.ReactNode | string>>>([]);

  useEffect(() => {
    const newRows: Array<Array<React.ReactNode | string>> = [];
    data.sparePartsRequests.forEach((spr) =>
      (showDeletedSprOnly ? spr.deletedParts : spr.parts).forEach((part) => {
        newRows.push([
          part.partNumber,
          part.description,
          part.quantity,
          part.createdAt.slice(0, 10),
          `${part.createdBy?.firstname} ${part.createdBy?.lastname}`,
          `#${spr.id}`,
          spr.status,
          part.comment || "---",
          <SprTrackerLink data={part?.trackings?.[0]} />,
        ]);
      })
    );
    setRows(newRows);
  }, [data.sparePartsRequests]);

  return (
    <div className="toc_detail_parts_table__wrapper">
      {rows.map((row, idx) => (
        <InfoCard
          data={[
            {
              title: "toc_parts.spr_table.spr_parts",
              value: row[0],
            },
            {
              title: "toc_parts.spr_table.description",
              value: row[1],
            },
            {
              title: "toc_parts.spr_table.quantity",
              value: row[2],
            },
            {
              title: "toc_parts.spr_table.created_at",
              value: row[3],
            },
            {
              title: "toc_parts.spr_table.created_by",
              value: row[4],
            },
            {
              title: "toc_parts.spr_table.spr",
              value: row[5],
            },
            {
              title: "toc_parts.spr_table.status",
              value: row[6],
            },
            {
              title: "toc_parts.spr_table.comment",
              value: row[7],
            },
            {
              title: "toc_parts.spr_table.tracking_number",
              value: row[8],
            },
          ]}
          dataCy={
            showDeletedSprOnly ? `toc-deleted-spr-${idx}` : `toc-spr-${idx}`
          }
        />
      ))}
    </div>
  );
}
