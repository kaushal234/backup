import React, { useState } from "react";
import "./CsrDetailDescription.css";
import {
  Accordion,
  AccordionDetails,
  AccordionSummary,
  Avatar,
  Button,
  ButtonBase,
  Chip,
  Divider,
  IconButton,
  Link,
  Paper,
  Typography,
} from "@mui/material";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import { useTranslation } from "react-i18next";
import dayjs from "dayjs";
import { useNavigate, useParams } from "react-router";
import CreateIcon from "@mui/icons-material/Create";
import SendIcon from "@mui/icons-material/Send";
import BarChartIcon from "@mui/icons-material/BarChart";
import DescriptionCard from "../DescriptionCard/DescriptionCard";
import { calculateDateDifference } from "../../utils/date";
import {
  COMMENT_TYPES,
  CSR_FILTER_TYPE_MAP,
  CSR_STATUS,
  CSR_TYPE,
  DATE_FORMAT,
  INTERVENTION_STATUS,
  SANITIZE_ALL,
} from "../../constants/constants";
import {
  IExtranetUser,
  IGetCustomerServiceRecordResponse,
} from "../../@type/IGetCustomerServiceRecordResponse";
import { ROUTES } from "../../constants/routes";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import CsrStatusProgress from "../CsrStatusProgress/CsrStatusProgress";
import FloatingActionButton from "../FloatingActionButton/FloatingActionButton";
import InterventionPopUp from "../InterventionPopUp/InterventionPopUp";
import CsrStatusPopUp from "../CsrStatusPopUp/CsrStatusPopUp";
import { formatContact, sanitize } from "../../utils/utils";
import FeatureComponent from "../FeatureComponent/FeatureComponent";
import CommentPopUp from "../CommentPopUp/CommentPopUp";
import { refreshCsrData } from "../../redux/slices/csrDetailSlice";

interface IProps {
  data: IGetCustomerServiceRecordResponse;
}

export default function CsrDetailDescription(props: IProps) {
  const { data } = props;
  const { t } = useTranslation();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const { csrId } = useParams();

  const factoryTime = useAppSelector((state) => state.time.factoryTime);

  const [isStatusPopUpVisible, setIsStatusPopUpVisible] = useState(false);
  const [isIntervenitonPopUpVisible, setIsIntervenitonPopUpVisible] =
    useState(false);
  const [isFactoryPopUpVisible, setIsFactoryPopUpVisible] = useState(false);

  const formattedCommissionedDate = data.equipmentRecord.dateCommissioned
    ? dayjs(data.equipmentRecord.dateCommissioned).format(DATE_FORMAT)
    : "---";

  const dayOpened = calculateDateDifference(data.createdAt).toString();

  let dayOpenedClassName;
  switch (true) {
    case +dayOpened > 10:
      dayOpenedClassName = "cui_bg-red";
      break;
    case +dayOpened > 2:
      dayOpenedClassName = "cui_bg-orange";
      break;
    default:
      dayOpenedClassName = "cui_bg-green";
  }

  const leaderFullName = `${data.openIntervention?.leader?.firstname ?? ""} ${
    data.openIntervention?.leader?.lastname ?? ""
  }`.trim();

  const handleProfileClick = (
    e: React.MouseEvent<HTMLAnchorElement, MouseEvent>
  ) => {
    e.preventDefault();
    navigate(`${ROUTES.profile}/${data.openIntervention?.leader?.id ?? ""}`);
  };

  const handleEdit = () => {
    navigate(`${ROUTES.csr.update}/${csrId}`);
  };

  const handleSurveyEdit = () => {
    navigate(`${ROUTES.csr.details}/${csrId}/${ROUTES.csr.survey.home}`);
  };

  const handleFactoryFlagPopUpClose = (isDataUpdated: boolean) => {
    setIsFactoryPopUpVisible(false);
    if (isDataUpdated) {
      dispatch(refreshCsrData());
    }
  };

  const handleContact = (contact: IExtranetUser | null) => {
    if (!contact) return;
    navigate(`${ROUTES.extranetUser}/${contact.id}`);
  };

  return (
    <div className="csr_detail_description__wrapper">
      <InterventionPopUp
        data={data}
        isOpen={isIntervenitonPopUpVisible}
        onClose={() => setIsIntervenitonPopUpVisible(false)}
        openingIntervention={
          data.openIntervention?.status === INTERVENTION_STATUS.PENDING
        }
        key={data.openIntervention?.status}
      />
      <CommentPopUp
        title="logs_preview.comment.flag_title"
        iri={data["@id"]}
        linkedTocIri={data.technicianOnCall?.["@id"]}
        commentType={COMMENT_TYPES.TOC_FROM_CSR}
        isOpen={isFactoryPopUpVisible}
        onClose={handleFactoryFlagPopUpClose}
        factoryFlag={data.technicianOnCall?.factoryFlag}
        showFactoryFlag
        disableFactoryFlag
        flipFactoryFlag
        confidentialToc={!!data.technicianOnCall?.confidential}
      />
      <CsrStatusPopUp
        isOpen={isStatusPopUpVisible}
        data={data}
        onClose={() => setIsStatusPopUpVisible(false)}
        key={data.status}
      />
      <div className="csr_detail_description__heading_wrapper">
        <Typography
          className="cui_light_text"
          variant="h5"
          gutterBottom
          data-cy="logs-heading"
        >
          {t("csr_details.heading")}
        </Typography>
        {(data.openIntervention?.status === INTERVENTION_STATUS.PENDING ||
          data.openIntervention?.status === INTERVENTION_STATUS.STARTED) && (
          <div>
            <Button
              className="cui_button primary"
              variant="contained"
              size="small"
              endIcon={<SendIcon />}
              onClick={() => setIsIntervenitonPopUpVisible(true)}
              data-cy="intervention-button"
            >
              {t("csr_details.intervention")}
            </Button>
          </div>
        )}
        {data.type === CSR_TYPE.commissioning &&
          (data.status === CSR_STATUS.CLOSED ||
            data.status === CSR_STATUS.COMPLETED) && (
            <Avatar className="csr_detail_description__survey" color="primary">
              <IconButton onClick={handleSurveyEdit} data-cy="csr-survey">
                <BarChartIcon />
              </IconButton>
            </Avatar>
          )}
      </div>
      <CsrStatusProgress
        status={data.status}
        onEdit={() => setIsStatusPopUpVisible(true)}
        showEdit={data.status === CSR_STATUS.COMPLETED}
      />
      <div className="csr_detail_description__row">
        <DescriptionCard
          title="csr_details.description.model"
          value={data.equipmentRecord.model}
          dataCy="csr-detail-main-info-model"
        />
        <DescriptionCard
          title="csr_details.description.additional_info.toc"
          value={data?.technicianOnCall?.id?.toString()}
          link={
            data?.technicianOnCall?.id
              ? `${ROUTES.toc.details}/${data.technicianOnCall.id}`
              : undefined
          }
          dataCy="csr-detail-main-info-toc"
        />
      </div>
      <div className="csr_detail_description__row">
        <DescriptionCard
          title="csr_details.description.serial_number"
          value={data.equipmentRecord.serialNumber}
          link={`/er/details/${data.equipmentRecord.id}`}
          dataCy="csr-detail-main-info-serial-number"
        />
        <DescriptionCard
          title="csr_details.description.airport"
          value={data.airport?.code}
          dataCy="csr-detail-main-info-airport-code"
        />
      </div>
      <div className="csr_detail_description__row">
        <DescriptionCard
          className={dayOpenedClassName}
          title="csr_details.description.days_opened"
          value={`${+dayOpened + 1}`}
          dataCy="csr-detail-main-info-days-open"
        />
        <DescriptionCard
          title="csr_details.description.type"
          value={CSR_FILTER_TYPE_MAP[data.type]}
          dataCy="csr-detail-main-info-type"
        />
      </div>
      {data.technicianOnCall && (
        <div className="csr_detail_description__row">
          <DescriptionCard
            title="csr_details.description.factory_flag.title"
            value={
              data.technicianOnCall.factoryFlag
                ? t("csr_details.description.factory_flag.value.open")
                : t("csr_details.description.factory_flag.value.close")
            }
            dataCy="csr-detail-main-info-factory-flag"
            onClick={
              data.technicianOnCall.factoryFlag
                ? undefined
                : () => setIsFactoryPopUpVisible(true)
            }
          />
          <DescriptionCard
            title="csr_details.description.factory_time.title"
            value={factoryTime}
            dataCy="csr-detail-main-info-factory-time"
          />
        </div>
      )}
      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography
              className="cui_bold-500"
              component="span"
              data-cy="csr-detail-accordion-title"
            >
              {sanitize(data.title || "---", SANITIZE_ALL)}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails data-cy="csr-detail-accordion-description">
            <span
              // eslint-disable-next-line react/no-danger
              dangerouslySetInnerHTML={{
                __html: sanitize(data.description),
              }}
            />
          </AccordionDetails>
        </Accordion>
      </Paper>

      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography className="cui_bold-500" component="span">
              {t("csr_details.description.additional_info.title")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails>
            <div className="csr_detail_description__info_row_wrapper">
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-created-by-title"
                >
                  {t("csr_details.description.additional_info.created_by")}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-created-by-value"
                >
                  {`${data.createdBy?.firstname ?? ""} ${
                    data.createdBy?.lastname ?? ""
                  }`}
                </div>
              </div>
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-created-at-title"
                >
                  {t("csr_details.description.additional_info.created_at")}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-created-at-value"
                >
                  {dayjs(data.createdAt).format(DATE_FORMAT)}
                </div>
              </div>
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-buyer-title"
                >
                  {t("csr_details.description.additional_info.buyer")}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-buyer-value"
                >
                  {data.equipmentRecord.buyer?.name ?? "---"}
                </div>
              </div>
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-user-title"
                >
                  {t("csr_details.description.additional_info.end_user")}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-user-value"
                >
                  {data.equipmentRecord.endUser?.name ?? "---"}
                </div>
              </div>
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-maintainer-title"
                >
                  {t("csr_details.description.additional_info.maintainer")}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-maintainer-value"
                >
                  {data.equipmentRecord.maintainer?.name ?? "---"}
                </div>
              </div>
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-hour-meter-title"
                >
                  {t("csr_details.description.additional_info.hour_meter")}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-hour-meter-value"
                >
                  {data.equipmentRecord.hourMeter ?? "---"}
                </div>
              </div>
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-manufacturing-location-title"
                >
                  {t(
                    "csr_details.description.additional_info.manufacturing_location"
                  )}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-manufacturing-location-value"
                >
                  {data.equipmentRecord.manufacturerLocation?.name ?? "---"}
                </div>
              </div>
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-sso-title"
                >
                  {t(
                    "csr_details.description.additional_info.sso_organisation"
                  )}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-sso-value"
                >
                  {data.equipmentRecord.salesOrganisation?.name ?? "---"}
                </div>
              </div>
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-sso-service-title"
                >
                  {t("csr_details.description.additional_info.sso_service")}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-sso-service-value"
                >
                  {data.equipmentRecord.salesOrganisationService?.name ?? "---"}
                </div>
              </div>
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-planned-at-title"
                >
                  {t("csr_details.description.additional_info.planned_at")}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-planned-at-value"
                >
                  {dayjs(data.openIntervention?.plannedAt).format(DATE_FORMAT)}
                </div>
              </div>
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-commisionned-date-title"
                >
                  {t("csr_details.description.commissioned_at")}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-commisionned-date-value"
                >
                  {formattedCommissionedDate}
                </div>
              </div>
            </div>
          </AccordionDetails>
        </Accordion>
      </Paper>

      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography
              className="cui_bold-500"
              component="span"
              data-cy="csr-detail-sub-accordion-technician-accordion-title"
            >
              {t("csr_details.description.technician_info.title")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails>
            <div className="csr_detail_description__info_row_wrapper">
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-full-name-title"
                >
                  {t("csr_details.description.technician_info.full_name")}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-full-name-value"
                >
                  {!leaderFullName && "---"}
                  {leaderFullName && (
                    <Link
                      className="csr_detail_description__info_cell"
                      href="#"
                      onClick={(e) => handleProfileClick(e)}
                    >
                      {leaderFullName}
                    </Link>
                  )}
                </div>
              </div>
              <div className="csr_detail_description__info_row">
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-email-title"
                >
                  {t("csr_details.description.technician_info.email")}
                </div>
                <div
                  className="csr_detail_description__info_cell"
                  data-cy="csr-detail-sub-accordion-email-value"
                >
                  {data.openIntervention?.leader?.email ?? "---"}
                </div>
              </div>
            </div>
          </AccordionDetails>
        </Accordion>
      </Paper>

      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography
              className="cui_bold-500"
              component="span"
              data-cy="csr-detail-sub-accordion-contact-title"
            >
              {t("csr_details.description.contact.title")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails>
            {data.contact ? (
              <ButtonBase onClick={() => handleContact(data.contact)}>
                <Chip
                  label={formatContact(data.contact)}
                  variant="outlined"
                  color="primary"
                  size="small"
                  data-cy="csr-detail-sub-accordion-contact-pill"
                />
              </ButtonBase>
            ) : (
              "---"
            )}
          </AccordionDetails>
        </Accordion>
      </Paper>
      <FeatureComponent features={["FEATURE_CUSTOMER_SERVICE_RECORD_EDIT"]}>
        <FloatingActionButton icon={<CreateIcon />} onClick={handleEdit} />
      </FeatureComponent>
    </div>
  );
}
