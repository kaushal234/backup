import React from "react";
import "./ServiceBulletinsPopUp.css";
import { Chip, Dialog, Typography } from "@mui/material";
import { t } from "i18next";
import { ITechnicianOnCall } from "../../@type/IGetTechnicalOnCallsResponse";
import { convertToTitleCase } from "../../utils/utils";

interface IProps {
  data: ITechnicianOnCall;
  isOpen: boolean;
  onClose: () => void;
}

export default function ServiceBulletinsPopUp(props: IProps) {
  const { data, isOpen, onClose } = props;

  return (
    <Dialog
      closeAfterTransition={false}
      className="service_bulletins_popup__wrapper"
      onClose={onClose}
      open={isOpen}
    >
      <Typography
        className="cui_light_text"
        variant="h5"
        gutterBottom
        data-cy="service-bulletins-popup-heading"
      >
        {t("toc_details.description.service_bulletins.title")}
      </Typography>
      <div className="service_bulletins_popup__list">
        {data.serviceBulletins.map((sb, idx) => (
          <div className="service_bulletins_popup__card">
            <div className="service_bulletins_popup__row">
              <div data-cy={`service-bulletins-${idx}-id`}>{`#${sb.id}`}</div>
              <div className="toc_detail_description__chip_wrapper">
                {sb.confidential !== "N" && (
                  <Chip
                    className="toc_detail_description__csr_open"
                    label={t(
                      "toc_details.description.service_bulletins.confidential"
                    )}
                    variant="outlined"
                    color="primary"
                    size="small"
                    data-cy={`service-bulletins-${idx}-confidential`}
                  />
                )}
                <Chip
                  className="toc_detail_description__csr_open"
                  label={convertToTitleCase(sb.type ?? "")}
                  variant="outlined"
                  color="primary"
                  size="small"
                  data-cy={`service-bulletins-${idx}-type`}
                />
                <Chip
                  className="toc_detail_description__csr_open"
                  label={convertToTitleCase(sb.status ?? "")}
                  variant="outlined"
                  color="primary"
                  size="small"
                  data-cy={`service-bulletins-${idx}-status`}
                />
              </div>
            </div>
            <Typography
              variant="button"
              className="cui_one_line"
              data-cy={`service-bulletins-${idx}-title`}
            >
              {sb.title}
            </Typography>
            <Typography
              variant="body1"
              gutterBottom
              data-cy={`service-bulletins-${idx}-description`}
            >
              {sb.description}
            </Typography>
          </div>
        ))}
      </div>
    </Dialog>
  );
}
