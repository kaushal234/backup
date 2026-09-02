import React from "react";
import { Box } from "@mui/material";
import { IPartTracking } from "../../@type/IGetTechnicalOnCallsResponse";
import "./SprTrackerLink.css";

interface IProps {
  data: IPartTracking | null;
}

export default function SprTrackerLink(props: IProps) {
  const { data } = props;

  const handleClick = () => {
    if (data?.trackingLink) {
      window.open(data.trackingLink, "_blank", "noopener,noreferrer");
    }
  };

  if (!data) return "---";

  return (
    <div>
      <Box
        className={`${data.trackingLink && "spr_tracker_link__wrapper"}`}
        onClick={handleClick}
      >
        <div>{data.trackingNumber}</div>
      </Box>
      <div>
        {`${data.carrier} (${data.shippedQuantity} ${data.unitOfMeasure})`}
      </div>
    </div>
  );
}
