import React from "react";
import "./CsrCard.css";
import { Paper, Typography } from "@mui/material";
import Tooltip from "../Tooltip/Tooltip";
import { ICustomerServiceRecord } from "../../@type/IGetAllCustomerServiceRecordResponse";
import { CSR_FILTER_TYPE_MAP, SANITIZE_ALL } from "../../constants/constants";
import { sanitize } from "../../utils/utils";

interface IProps {
  csr: ICustomerServiceRecord;
  onClick?: (item: ICustomerServiceRecord) => void;
}

function CsrCard(props: IProps) {
  const {
    csr,
    onClick: handleClick = () => {
      /* empty on purpose */
    },
  } = props;

  return (
    <Paper
      elevation={1}
      className="csr_card__wrapper"
      onClick={() => handleClick(csr)}
      data-cy="csr-card"
    >
      <div className="csr_card__left_section">
        <div className="csr_card__first_row">
          <Typography
            className="csr_card__first_row_left"
            variant="button"
            gutterBottom
            data-cy="csr-card-value"
          >
            {`#${csr.id ?? ""}`}
          </Typography>

          <Typography
            className="csr_card__first_row_mid"
            variant="subtitle1"
            gutterBottom
            data-cy="csr-card-value"
          >
            {csr.equipmentRecord.endUser?.name ?? ""}
          </Typography>
          <div className="csr_card__first_row_right" />
        </div>
        <div className="csr_card__middle_row">
          <div>
            <Tooltip title={csr.description}>
              <Typography
                variant="button"
                className="cui_one_line"
                gutterBottom
                data-cy="csr-card-value"
              >
                {sanitize(csr.title || "---", SANITIZE_ALL)}
              </Typography>
            </Tooltip>
            <Tooltip title={csr.description}>
              <Typography
                variant="body1"
                className="cui_three_line"
                gutterBottom
                data-cy="csr-card-value"
              >
                <span
                  // eslint-disable-next-line react/no-danger
                  dangerouslySetInnerHTML={{
                    __html: sanitize(csr.description),
                  }}
                />
              </Typography>
            </Tooltip>
          </div>
        </div>
        <div className="csr_card__details">
          <Typography variant="caption" gutterBottom data-cy="csr-card-value">
            {`${csr.equipmentRecord.serialNumber.toUpperCase()}`}
          </Typography>
          <Typography variant="caption" gutterBottom data-cy="csr-card-value">
            {csr.equipmentRecord.model ?? ""}
          </Typography>
          <Typography variant="caption" gutterBottom data-cy="csr-card-value">
            {csr.airport?.code ?? ""}
          </Typography>
          <Typography variant="caption" gutterBottom data-cy="csr-card-value">
            {CSR_FILTER_TYPE_MAP[csr.type]}
          </Typography>
        </div>
      </div>
    </Paper>
  );
}

export default CsrCard;
