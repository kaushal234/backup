import React, { useEffect, useState } from "react";
import "./TocCard.css";
import { Chip, Paper, Skeleton, Typography } from "@mui/material";
import NoPhotographyIcon from "@mui/icons-material/NoPhotography";
import { useTranslation } from "react-i18next";
import OutlinedFlagIcon from "@mui/icons-material/OutlinedFlag";
import { ITechnicianOnCall } from "../../@type/IGetAllTechnicalOnCallsResponse";
import { getTechnicalOnCallMainFileById } from "../../api/getTechnicalOnCallMainFileById";
import { sanitize } from "../../utils/utils";
import { SANITIZE_ALL } from "../../constants/constants";

interface IProps {
  toc: ITechnicianOnCall;
  onClick?: (item: ITechnicianOnCall) => void;
}

function TocCard(props: IProps) {
  const {
    toc,
    onClick: handleClick = () => {
      /* empty on purpose */
    },
  } = props;
  const { t } = useTranslation();
  const [imageUrl, setImageUrl] = useState<string | null>(null);

  const fetchThumbnail = async () => {
    if (toc.id && toc.mainFile?.id) {
      const response = await getTechnicalOnCallMainFileById({
        tocId: toc.id.toString(),
        fileId: toc.mainFile.id.toString(),
      });
      setImageUrl(response.data?.url ?? "");
    } else {
      setImageUrl("");
    }
  };

  useEffect(() => {
    fetchThumbnail();
  }, [toc.mainFile]);

  return (
    <Paper
      elevation={1}
      className={`toc_card__wrapper ${
        toc.openDaysStatus === "OUTDATED" && "toc_card__outdated"
      }`}
      onClick={() => handleClick(toc)}
      data-cy="toc-card"
    >
      <div className="toc_card__left_section">
        <div className="toc_card__first_row">
          <Typography
            className="toc_card__first_row_left"
            variant="button"
            gutterBottom
            data-cy="toc-card-value"
          >
            {`#${toc.id ?? ""}`}
          </Typography>

          <Typography
            className="toc_card__first_row_mid cui_one_line"
            variant="subtitle1"
            gutterBottom
            data-cy="toc-card-value"
          >
            {toc.equipmentRecord?.endUser?.name ?? ""}
          </Typography>
          <div className="toc_card__first_row_right">
            {toc.factoryFlag && (
              <Chip
                color="error"
                size="small"
                variant="outlined"
                label={t("Factory")}
                onDelete={() => {
                  /* Empty On Purpose */
                }}
                deleteIcon={<OutlinedFlagIcon />}
                data-cy="toc-card-factory-flag"
              />
            )}
          </div>
        </div>
        <div className="toc_card__middle_row">
          <div>
            <Typography
              variant="button"
              className="cui_one_line"
              gutterBottom
              data-cy="toc-card-value"
            >
              {sanitize(toc.title, SANITIZE_ALL)}
            </Typography>

            <Typography
              variant="body1"
              className="cui_three_line"
              gutterBottom
              data-cy="toc-card-value"
            >
              <span
                className="toc_card_description_wrapper"
                // eslint-disable-next-line react/no-danger
                dangerouslySetInnerHTML={{
                  __html: sanitize(toc.description),
                }}
              />
            </Typography>
          </div>
          <div className="toc_card__image_wrapper" data-cy="toc-card-image">
            {imageUrl === null && (
              <Skeleton variant="rounded" width={80} height={80} />
            )}
            {imageUrl === "" && (
              <div className="toc_card__no_image_wrapper">
                <NoPhotographyIcon />
              </div>
            )}
            {imageUrl && (
              <img
                src={imageUrl}
                className="toc_card__image"
                alt="toc thumbnail"
              />
            )}
          </div>
        </div>
        <div className="toc__card_details">
          <Typography variant="caption" gutterBottom data-cy="toc-card-value">
            {`${toc.equipmentRecord?.serialNumber.toUpperCase()}`}
          </Typography>
          <Typography variant="caption" gutterBottom data-cy="toc-card-value">
            {toc.equipmentRecord?.model ?? ""}
          </Typography>
          <Typography variant="caption" gutterBottom data-cy="toc-card-value">
            {toc.airport.code ?? ""}
          </Typography>
          <Typography variant="caption" gutterBottom data-cy="toc-card-value">
            {toc.indiceFactor ?? ""}
          </Typography>
        </div>
      </div>
    </Paper>
  );
}

export default TocCard;
