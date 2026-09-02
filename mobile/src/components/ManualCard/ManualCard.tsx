import React from "react";
import "./ManualCard.css";
import { Paper, Typography } from "@mui/material";
import { IManual } from "../../@type/IGetEquipmentRecordResponse";
import { convertToTitleCase } from "../../utils/utils";
import { formatDateTime } from "../../utils/date";

interface IProps {
  data: IManual;
  onClick?: (item: IManual) => void;
}

function ManualCard(props: IProps) {
  const {
    data,
    onClick: handleClick = () => {
      /* empty on purpose */
    },
  } = props;

  return (
    <Paper
      elevation={1}
      className="manual_card__wrapper"
      onClick={() => handleClick(data)}
      data-cy="manual-card"
    >
      <div className="manual_card__left_section">
        <div className="manual_card__first_row">
          <Typography variant="button" gutterBottom data-cy="manual-card-value">
            {data.id?.toString() ?? "---"}
          </Typography>
          <div className="manual_card__first_row_right" />
        </div>
        <div className="manual_card__middle_row">
          <div>
            <Typography
              variant="button"
              className="cui_three_line"
              gutterBottom
              data-cy="manual-card-value"
            >
              {data.description ?? "---"}
            </Typography>
          </div>
        </div>
        <div className="manual_card__details">
          <Typography
            variant="caption"
            gutterBottom
            data-cy="manual-card-value"
          >
            {convertToTitleCase(data.status ?? "---")}
          </Typography>
          <Typography
            variant="caption"
            gutterBottom
            data-cy="manual-card-value"
          >
            {data.language ?? "---"}
          </Typography>
          <Typography
            variant="caption"
            gutterBottom
            data-cy="manual-card-value"
          >
            {data.createdAt ? formatDateTime(data.createdAt) : "---"}
          </Typography>
        </div>
      </div>
    </Paper>
  );
}

export default ManualCard;
