import React from "react";
import "./ErCard.css";
import { Paper, Typography } from "@mui/material";
import { IEquipmentRecord } from "../../@type/IGetAllEquipmentRecordResponse";

interface IProps {
  data: IEquipmentRecord;
  onClick?: (item: IEquipmentRecord) => void;
}

function ErCard(props: IProps) {
  const {
    data,
    onClick: handleClick = () => {
      /* empty on purpose */
    },
  } = props;

  return (
    <Paper
      elevation={1}
      className="er_card__wrapper"
      onClick={() => handleClick(data)}
      data-cy="er-card"
    >
      <div className="er_card__left_section">
        <div className="er_card__first_row">
          <Typography variant="button" gutterBottom data-cy="er-card-value">
            {data.serialNumber.toLocaleUpperCase()}
          </Typography>
        </div>
        <div className="er_card__middle_row">
          <div>
            <Typography
              variant="button"
              className="cui_one_line"
              gutterBottom
              data-cy="er-card-value"
            >
              {data.type ?? ""}
            </Typography>
            <Typography
              variant="body1"
              className="cui_one_line"
              gutterBottom
              data-cy="er-card-value"
            >
              {data.model ?? ""}
            </Typography>
          </div>
        </div>
        <div className="er_card__details">
          <Typography variant="caption" gutterBottom data-cy="er-card-value">
            {data.airport?.code ?? "---"}
          </Typography>
          <Typography variant="caption" gutterBottom data-cy="er-card-value">
            0 CSR
          </Typography>
          <Typography variant="caption" gutterBottom data-cy="er-card-value">
            0 TOC
          </Typography>
        </div>
      </div>
    </Paper>
  );
}

export default ErCard;
