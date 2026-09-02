import React from "react";
import "./TocDetailParts.css";
import { useTranslation } from "react-i18next";
import {
  Accordion,
  AccordionDetails,
  AccordionSummary,
  Button,
  Divider,
  Typography,
} from "@mui/material";
import AddIcon from "@mui/icons-material/Add";
import { useNavigate, useParams } from "react-router";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import { ITechnicianOnCall } from "../../@type/IGetTechnicalOnCallsResponse";
import TocDetailPartsCards from "../TocDetailPartsCards/TocDetailPartsCards";
import { ROUTES } from "../../constants/routes";
import TocDetailSprCards from "../TocDetailSprCards/TocDetailSprCards";
import FeatureComponent from "../FeatureComponent/FeatureComponent";
import TocDetailDefectivePartsCards from "../TocDetailDefectivePartsCards/TocDetailDefectivePartsCards";

interface IProps {
  data: ITechnicianOnCall;
}

export default function TocDetailParts(props: IProps) {
  const { data } = props;
  const { tocId } = useParams();
  const { t } = useTranslation();
  const navigate = useNavigate();

  const handleAddParts = () => {
    navigate(`${ROUTES.toc.details}/${tocId}/${ROUTES.toc.parts.create}`);
  };

  const handleAddSpr = () => {
    navigate(`${ROUTES.toc.details}/${tocId}/${ROUTES.toc.spr.create}`);
  };

  const hasSpr = data.sparePartsRequests.some((spr) => spr.parts.length !== 0);

  const hasDeletedSpr = data.sparePartsRequests.some(
    (spr) => spr.deletedParts.length !== 0
  );

  return (
    <div className="toc_detail_parts__wrapper">
      {data.defectiveParts.length !== 0 && (
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography
              component="span"
              data-cy="toc-detail-defective-parts-heading"
            >
              {t("toc_parts.defective_parts")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails>
            <div>
              <div className="toc_detail_parts__content">
                <TocDetailDefectivePartsCards data={data} />
              </div>
            </div>
          </AccordionDetails>
        </Accordion>
      )}
      <Accordion defaultExpanded>
        <AccordionSummary expandIcon={<ExpandMoreIcon />}>
          <Typography component="span" data-cy="toc-detail-parts-heading">
            {t("toc_parts.heading")}
          </Typography>
        </AccordionSummary>
        <Divider />
        <AccordionDetails>
          <div>
            <div className="toc_detail_parts__action_wrapper">
              <div className="toc_detail_parts__no_parts">
                {!data.parts.length && (
                  <Typography
                    variant="body1"
                    gutterBottom
                    data-cy="toc-detail-parts-no-content"
                  >
                    {t("toc_parts.no_content")}
                  </Typography>
                )}
              </div>
              <Button
                className="toc_detail_parts__action"
                variant="contained"
                endIcon={<AddIcon />}
                data-cy="toc-detail-parts-add"
                onClick={handleAddParts}
              >
                {t("toc_parts.add.parts")}
              </Button>
            </div>
            {data.parts.length !== 0 && (
              <div className="toc_detail_parts__content">
                <TocDetailPartsCards data={data} />
              </div>
            )}
          </div>
        </AccordionDetails>
      </Accordion>
      <Accordion defaultExpanded>
        <AccordionSummary expandIcon={<ExpandMoreIcon />}>
          <Typography component="span" data-cy="toc-detail-spr-heading">
            {t("toc_parts.spr_heading")}
          </Typography>
        </AccordionSummary>
        <Divider />
        <AccordionDetails>
          <div>
            <div className="toc_detail_parts__action_wrapper">
              <div className="toc_detail_parts__no_parts">
                {!hasSpr && (
                  <Typography
                    variant="body1"
                    gutterBottom
                    data-cy="toc-detail-parts-spr-no-content"
                  >
                    {t("toc_parts.no_spr_content")}
                  </Typography>
                )}
              </div>
              <FeatureComponent
                features={["FEATURE_SPARE_PARTS_REQUESTS_CREATE", "MOO_SPR"]}
              >
                <Button
                  className="toc_detail_parts__action"
                  variant="contained"
                  endIcon={<AddIcon />}
                  onClick={handleAddSpr}
                  data-cy="toc-detail-spr-parts-add"
                >
                  {t("toc_parts.add.spr")}
                </Button>
              </FeatureComponent>
            </div>

            {hasSpr && (
              <div className="toc_detail_parts__content">
                <TocDetailSprCards data={data} />
              </div>
            )}
          </div>
        </AccordionDetails>
      </Accordion>
      <Accordion defaultExpanded>
        <AccordionSummary expandIcon={<ExpandMoreIcon />}>
          <Typography component="span" data-cy="toc-detail-deleted-spr-heading">
            {t("toc_parts.deleted_spr_heading")}
          </Typography>
        </AccordionSummary>
        <Divider />
        <AccordionDetails>
          <div>
            <div className="toc_detail_parts__action_wrapper">
              <div className="toc_detail_parts__no_parts">
                {!hasDeletedSpr && (
                  <Typography
                    variant="body1"
                    gutterBottom
                    data-cy="toc-detail-parts-deleted-spr-no-content"
                  >
                    {t("toc_parts.no_deleted_spr_content")}
                  </Typography>
                )}
              </div>
            </div>

            {hasDeletedSpr && (
              <div className="toc_detail_parts__content">
                <TocDetailSprCards data={data} showDeletedSprOnly />
              </div>
            )}
          </div>
        </AccordionDetails>
      </Accordion>
    </div>
  );
}
