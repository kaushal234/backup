import React, { useEffect, useState } from "react";
import "./TocDetailDescription.css";
import {
  Accordion,
  AccordionDetails,
  AccordionSummary,
  Box,
  ButtonBase,
  Chip,
  Collapse,
  Divider,
  IconButton,
  Paper,
  Typography,
} from "@mui/material";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import { useTranslation } from "react-i18next";
import dayjs from "dayjs";
import { useNavigate, useParams } from "react-router";
import CreateIcon from "@mui/icons-material/Create";
import TranslateIcon from "@mui/icons-material/Translate";
import {
  IExtranetUser,
  ITechnicianOnCall,
} from "../../@type/IGetTechnicalOnCallsResponse";
import DescriptionCard from "../DescriptionCard/DescriptionCard";
import {
  COMMENT_TYPES,
  CSR_FILTER_STATUS_MAP,
  DATE_FORMAT,
  SANITIZE_ALL,
  TOC_FILTER_TAG_MAP,
  TOC_STATUS,
} from "../../constants/constants";
import { ROUTES } from "../../constants/routes";
import TocStatusProgress from "../TocStatusProgress/TocStatusProgress";
import TocStatusPopUp from "../TocStatusPopUp/TocStatusPopUp";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import FloatingActionButton from "../FloatingActionButton/FloatingActionButton";
import RequestCsrPopUp from "../RequestCsrPopUp/RequestCsrPopUp";
import {
  refreshTocData,
  setShowUpdateTocStatusPopup,
} from "../../redux/slices/tocDetailSlice";
import {
  convertToTitleCase,
  formatContact,
  formatTocLinks,
  sanitize,
} from "../../utils/utils";
import FeatureComponent from "../FeatureComponent/FeatureComponent";
import { useFeature } from "../../hooks/useFeature";
import ServiceBulletinsPopUp from "../ServiceBulletinsPopUp/ServiceBulletinsPopUp";
import CommentPopUp from "../CommentPopUp/CommentPopUp";
import UpdateUnitOperationalStatusPopUp from "../UpdateUnitOperationalStatusPopUp/UpdateUnitOperationalStatusPopUp";

interface IProps {
  data: ITechnicianOnCall;
}

export default function TocDetailDescription(props: IProps) {
  const { data } = props;
  const dispatch = useAppDispatch();
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { tocId } = useParams();
  const [showOriginalTitle, setShowOriginalTitle] = useState(false);
  const [showOriginalDescription, setShowOriginalDescription] = useState(false);
  const [showOriginalSymptoms, setShowOriginalSymptoms] = useState(false);
  const [showOriginalRootCause, setShowOriginalRootCause] = useState(false);
  const [showOriginalSolution, setShowOriginalSolution] = useState(false);

  const isAuthorisedToRequestCsr = useFeature({
    features: ["FEATURE_CUSTOMER_SERVICE_RECORD_CREATE"],
  });

  const factoryTime = useAppSelector((state) => state.time.factoryTime);
  const nmcTime = useAppSelector((state) => state.time.nmcTime);

  const showUpdateTocStatusPopup = useAppSelector(
    (state) => state.tocDetail.showUpdateTocStatusPopup
  );

  const [isStatusPopUpVisible, setIsStatusPopUpVisible] = useState(false);
  const [isCsrPopUpVisible, setIsCsrPopUpVisible] = useState(false);
  const [isSbPopUpVisible, setIsSbPopUpVisible] = useState(false);
  const [isFactoryPopUpVisible, setIsFactoryPopUpVisible] = useState(false);
  const [
    isUnitOperationalStatusPopUpVisible,
    setIsUnitOperationalStatusPopUpVisible,
  ] = useState(false);

  const formattedCommissionedDate = data.equipmentRecord?.dateCommissioned
    ? dayjs(data.equipmentRecord.dateCommissioned).format(DATE_FORMAT)
    : "---";

  let dayWithoutActivityClassName = "cui_bg-green";
  if (data.daysWithoutActivityStatus === "OUTDATED") {
    dayWithoutActivityClassName = "cui_bg-red";
  }

  const unitOperationalStatus = data.unitOperationalStatus?.name;

  let unitOperationalStatusClassName;
  switch (unitOperationalStatus) {
    case "NMC":
      unitOperationalStatusClassName = "cui_bg-red";
      break;
    case "MCP":
      unitOperationalStatusClassName = "cui_bg-orange";
      break;
    default:
      unitOperationalStatusClassName = "cui_bg-green";
  }

  let warrantyClassName;
  switch (data.warrantyInformation) {
    case "EXPIRED":
      unitOperationalStatusClassName = "cui_bg-red";
      break;
    case "EFFECTIVE":
      unitOperationalStatusClassName = "cui_bg-green";
      break;
    default:
      warrantyClassName = "cui_bg-orange";
  }

  const canRequestCsr =
    data.status !== TOC_STATUS.SOLVED &&
    data.status !== TOC_STATUS.CLOSED &&
    !data.currentCustomerServiceRecord &&
    !data.customerServiceRecords.length &&
    data.equipmentRecord &&
    isAuthorisedToRequestCsr;

  const canUpdateFactoryFlag =
    data.status !== TOC_STATUS.SOLVED && data.status !== TOC_STATUS.CLOSED;

  const handleToggleOriginalTitle = (event: React.MouseEvent) => {
    event.stopPropagation();
    setShowOriginalTitle((prev) => !prev);
  };

  const handleToggleOriginalDescription = () => {
    setShowOriginalDescription((prev) => !prev);
  };

  const handleToggleOriginalSymptoms = () => {
    setShowOriginalSymptoms((prev) => !prev);
  };

  const handleToggleOriginalRootCause = () => {
    setShowOriginalRootCause((prev) => !prev);
  };

  const handleToggleOriginalSolution = () => {
    setShowOriginalSolution((prev) => !prev);
  };

  const handleEdit = () => {
    navigate(`${ROUTES.toc.update}/${tocId}`);
  };

  const handleRequestCsr = () => {
    setIsCsrPopUpVisible(true);
  };

  const handleUpdateFactoryFlag = () => {
    setIsFactoryPopUpVisible(true);
  };

  const handleCsrClick = (id: string) => {
    navigate(`${ROUTES.csr.details}/${id}`);
  };

  const handleViewSbPopUp = () => {
    setIsSbPopUpVisible(true);
  };

  const handleFactoryFlagPopUpClose = (isDataUpdated: boolean) => {
    setIsFactoryPopUpVisible(false);
    if (isDataUpdated) {
      dispatch(refreshTocData());
    }
  };

  const handleContact = (contact: IExtranetUser | null) => {
    if (!contact) return;
    navigate(`${ROUTES.extranetUser}/${contact.id}`);
  };

  useEffect(() => {
    if (showUpdateTocStatusPopup) {
      setIsStatusPopUpVisible(true);
      dispatch(setShowUpdateTocStatusPopup(false));
    }
  }, []);

  let csrValue: string | undefined;
  let csrLink: string | undefined;
  const csrId = data?.currentCustomerServiceRecord?.id?.toString();
  const csrCount = data?.customerServiceRecords?.length;

  if (canRequestCsr) {
    csrValue = t("toc_details.description.request");
  } else if (data?.currentCustomerServiceRecord?.id) {
    csrValue = csrId;
    csrLink = `${ROUTES.csr.details}/${csrId}`;
  } else if (csrCount) {
    csrValue = `${t("toc_details.description.view")} (${csrCount})`;
    csrLink = "#csr-list";
  }

  const links = formatTocLinks(data.links);

  return (
    <div className="toc_detail_description__wrapper">
      <TocStatusPopUp
        isOpen={isStatusPopUpVisible}
        data={data}
        onClose={() => setIsStatusPopUpVisible(false)}
        key={data.status}
      />
      <RequestCsrPopUp
        isOpen={isCsrPopUpVisible}
        data={data}
        onClose={() => setIsCsrPopUpVisible(false)}
      />
      <ServiceBulletinsPopUp
        isOpen={isSbPopUpVisible}
        data={data}
        onClose={() => setIsSbPopUpVisible(false)}
      />
      <TocStatusProgress
        data={data}
        onEdit={() => setIsStatusPopUpVisible(true)}
      />
      <CommentPopUp
        title="toc_details.factory_flag_popup.heading"
        iri={data["@id"]}
        commentType={COMMENT_TYPES.TOC}
        isOpen={isFactoryPopUpVisible}
        onClose={handleFactoryFlagPopUpClose}
        factoryFlag={data.factoryFlag}
        showFactoryFlag
        disableFactoryFlag
        flipFactoryFlag
        confidentialToc={data.confidential}
      />
      <UpdateUnitOperationalStatusPopUp
        isOpen={isUnitOperationalStatusPopUpVisible}
        data={data}
        onClose={() => setIsUnitOperationalStatusPopUpVisible(false)}
      />
      <div className="toc_detail_description__row">
        <DescriptionCard
          title="toc_details.description.model"
          value={data.equipmentRecord?.model}
          dataCy="toc-detail-main-info-model"
        />
        <DescriptionCard
          title="toc_details.description.additional_info.csr"
          value={csrValue}
          link={csrLink}
          onClick={canRequestCsr ? handleRequestCsr : undefined}
          dataCy="toc-detail-main-info-csr"
        />
      </div>
      <div className="toc_detail_description__row">
        <DescriptionCard
          title="toc_details.description.additional_info.equipment_record"
          value={data.equipmentRecord?.serialNumber}
          link={
            data.equipmentRecord?.serialNumber
              ? `/er/details/${data.equipmentRecord?.id}`
              : ""
          }
          dataCy="toc-detail-main-info-serial-number"
        />
        <DescriptionCard
          title="toc_details.description.airport"
          value={data.airport.code}
          dataCy="toc-detail-main-info-airport-code"
        />
      </div>
      <div className="toc_detail_description__row">
        <DescriptionCard
          className={dayWithoutActivityClassName}
          title="toc_details.description.days_opened"
          value={`${data.openDays}`}
          dataCy="toc-detail-main-info-days-open"
        />
        <DescriptionCard
          title="toc_details.description.additional_info.ifactor"
          value={data.indiceFactor}
          dataCy="toc-detail-main-info-ifactor"
        />
      </div>
      <div className="toc_detail_description__row">
        <DescriptionCard
          className={unitOperationalStatusClassName}
          title="toc_details.description.unit_operational_status"
          value={unitOperationalStatus}
          dataCy="toc-detail-main-info-unit-operational-status"
          onClick={() => setIsUnitOperationalStatusPopUpVisible(true)}
        />
        <DescriptionCard
          className={unitOperationalStatusClassName}
          title="toc_details.description.time_nmc"
          value={nmcTime}
          dataCy="toc-detail-main-info-time-nmc"
        />
      </div>
      <div className="toc_detail_description__row">
        <DescriptionCard
          title="toc_details.description.factory_flag.title"
          value={
            data.factoryFlag
              ? t("toc_details.description.factory_flag.value.open")
              : t("toc_details.description.factory_flag.value.close")
          }
          dataCy="toc-detail-main-info-factory-flag"
          onClick={canUpdateFactoryFlag ? handleUpdateFactoryFlag : undefined}
        />
        <DescriptionCard
          title="toc_details.description.factory_time.title"
          value={factoryTime ?? ""}
          dataCy="toc-detail-main-info-factory-time"
        />
      </div>
      <div className="toc_detail_description__row">
        <DescriptionCard
          className={warrantyClassName}
          title="toc_details.description.warranty"
          value={convertToTitleCase(data.warrantyInformation ?? "---")}
          dataCy="toc-detail-main-info-warranty"
        />
        {data.serviceBulletins.length !== 0 && (
          <DescriptionCard
            title="toc_details.description.service_bulletins.title"
            value={t("toc_details.description.service_bulletins.value")}
            dataCy="toc-detail-main-info-service-bulletins"
            onClick={handleViewSbPopUp}
          />
        )}
      </div>
      <div>
        <Paper elevation={1}>
          <Accordion defaultExpanded>
            <AccordionSummary expandIcon={<ExpandMoreIcon />}>
              <div className="toc_detail_description__title_header">
                <div className="toc_detail_description__title_row">
                  <Typography
                    className="cui_bold-500 toc_detail_description__title_text"
                    component="span"
                    data-cy="toc-detail-accordion-title"
                  >
                    {sanitize(data.title, SANITIZE_ALL)}
                  </Typography>
                  {data.originalTitle && data.originalTitle !== data.title && (
                    <IconButton
                      size="small"
                      onClick={handleToggleOriginalTitle}
                      data-cy="toc-detail-original-title-toggle"
                    >
                      <TranslateIcon fontSize="small" />
                    </IconButton>
                  )}
                </div>
                {data.originalTitle && data.originalTitle !== data.title && (
                  <Collapse in={showOriginalTitle}>
                    <Typography
                      variant="body2"
                      color="text.secondary"
                      className="toc_detail_description__original_title"
                    >
                      {sanitize(data.originalTitle, SANITIZE_ALL)}
                    </Typography>
                  </Collapse>
                )}
              </div>
            </AccordionSummary>
            <Divider />
            <AccordionDetails data-cy="toc-detail-accordion-description">
              <div className="toc_detail_description__description_row">
                <span
                  className="toc_detail_description__description_text"
                  // eslint-disable-next-line react/no-danger
                  dangerouslySetInnerHTML={{
                    __html: sanitize(data.description),
                  }}
                />
                {data.originalDescription &&
                  data.originalDescription !== data.description && (
                    <IconButton
                      size="small"
                      onClick={handleToggleOriginalDescription}
                      data-cy="toc-detail-original-description-toggle"
                    >
                      <TranslateIcon fontSize="small" />
                    </IconButton>
                  )}
              </div>
              {data.originalDescription &&
                data.originalDescription !== data.description && (
                  <Collapse in={showOriginalDescription}>
                    <Divider className="toc_detail_description__description_divider" />
                    <span
                      className="toc_detail_description__original_description"
                      // eslint-disable-next-line react/no-danger
                      dangerouslySetInnerHTML={{
                        __html: sanitize(data.originalDescription),
                      }}
                    />
                  </Collapse>
                )}
            </AccordionDetails>
          </Accordion>
        </Paper>
      </div>

      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography className="cui_bold-500" component="span">
              {t("toc_details.description.additional_info.title")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails>
            <div className="toc_detail_description__info_row_wrapper">
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-assignee-title"
                >
                  {t("toc_details.description.additional_info.assignee")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-assignee-value"
                >
                  {`${data.assignee?.firstname ?? ""} ${
                    data.assignee?.lastname ?? ""
                  }`}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-technician-title"
                >
                  {t("toc_details.description.additional_info.technician")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-technician-value"
                >
                  {`${data.technician?.firstname ?? ""} ${
                    data.technician?.lastname ?? ""
                  }`}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-error-code-title"
                >
                  {t("toc_details.description.additional_info.error_codes")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-error-code-value"
                >
                  {data.errorCodes || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-emission-rating-title"
                >
                  {t("toc_details.description.additional_info.emission_rating")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-emission-rating-value"
                >
                  {data.equipmentRecord?.emissionRating?.name || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-service-activity-title"
                >
                  {t(
                    "toc_details.description.additional_info.service_activity"
                  )}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-service-activity-value"
                >
                  {data.serviceActivity.description}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-payer-title"
                >
                  {t("toc_details.description.additional_info.who_pays")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-payer-value"
                >
                  {t(data.technicianOnCallType.name)}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-created-by-title"
                >
                  {t("toc_details.description.additional_info.created_by")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-created-by-value"
                >
                  {`${data.createdBy?.firstname ?? ""} ${
                    data.createdBy?.lastname ?? ""
                  }`}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-created-at-title"
                >
                  {t("toc_details.description.additional_info.created_at")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-created-at-value"
                >
                  {dayjs(data.createdAt).format(DATE_FORMAT)}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-buyer-title"
                >
                  {t("toc_details.description.additional_info.buyer")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-buyer-value"
                >
                  {data.equipmentRecord?.buyer?.name || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-user-title"
                >
                  {t("toc_details.description.additional_info.end_user")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-user-value"
                >
                  {data.equipmentRecord?.endUser?.name || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-maintainer-title"
                >
                  {t("toc_details.description.additional_info.maintainer")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-maintainer-value"
                >
                  {data.equipmentRecord?.maintainer?.name || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-hour-meter-title"
                >
                  {t("toc_details.description.additional_info.hour_meter")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-hour-meter-value"
                >
                  {data.equipmentRecord?.hourMeter || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-manufacturing-location-title"
                >
                  {t(
                    "toc_details.description.additional_info.manufacturing_location"
                  )}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-manufacturing-location-value"
                >
                  {data.equipmentRecord?.manufacturerLocation?.name || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-sso-title"
                >
                  {t(
                    "toc_details.description.additional_info.sso_organisation"
                  )}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-sso-value"
                >
                  {data.equipmentRecord?.salesOrganisation?.name || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-sso-service-title"
                >
                  {t("toc_details.description.additional_info.sso_service")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-sso-service-value"
                >
                  {data.salesOrganisationService?.name || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-tags-title"
                >
                  {t("toc_details.description.additional_info.tags")}
                </div>
                <div
                  className="toc_detail_description__info_cell toc_detail_description__tag_wrapper"
                  data-cy="toc-detail-sub-accordion-tags-value"
                >
                  {data.tags.length
                    ? data.tags.map((tag) => {
                        const shortTag = tag.name.replace("toc.tags.", "");
                        const tagName =
                          TOC_FILTER_TAG_MAP[shortTag] ?? tag.name;
                        return (
                          <Chip
                            key={tagName}
                            label={tagName}
                            variant="outlined"
                            color="primary"
                            size="small"
                            data-cy="toc-detail-sub-accordion-tag-pill"
                          />
                        );
                      })
                    : "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-commissioned-date-title"
                >
                  {t("toc_details.description.commissioned_at")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-commissioned-date-value"
                >
                  {formattedCommissionedDate}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-customer-title"
                >
                  {t("toc_details.description.additional_info.customer")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-customer-value"
                >
                  {data.customer.name || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-third-party-name-title"
                >
                  {t(
                    "toc_details.description.additional_info.third_party_name"
                  )}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-third-party-name-value"
                >
                  {data.thirdPartyName || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-third-party-ref-title"
                >
                  {t("toc_details.description.additional_info.third_party_ref")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-third-party-ref-value"
                >
                  {data.thirdPartyRef || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-serial-number-title"
                >
                  {t("toc_details.description.additional_info.serial_number")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-serial-number-value"
                >
                  {data.serialNumber || "---"}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-commissioned-date-title"
                >
                  {t("toc_details.description.additional_info.confidential")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-commissioned-date-value"
                >
                  {data.confidential
                    ? t("common.boolean.yes")
                    : t("common.boolean.no")}
                </div>
              </div>
              <div className="toc_detail_description__info_row">
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-outdated-title"
                >
                  {t("toc_details.description.additional_info.outdated")}
                </div>
                <div
                  className="toc_detail_description__info_cell"
                  data-cy="toc-detail-sub-accordion-outdated-value"
                >
                  {`${data.daysWithoutActivity} days / ${data.indiceFactor}`}
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
              data-cy="toc-detail-sub-accordion-symptoms-title"
            >
              {t("toc_details.description.symptoms")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails data-cy="toc-detail-sub-accordion-symptoms-value">
            <div className="toc_detail_description__title_row">
              <Typography component="span">
                {data.symptoms ? data.symptoms : "---"}
              </Typography>
              {data.originalSymptoms &&
                data.originalSymptoms !== data.symptoms && (
                  <IconButton
                    size="small"
                    onClick={handleToggleOriginalSymptoms}
                    data-cy="toc-detail-original-symptoms-toggle"
                  >
                    <TranslateIcon fontSize="small" />
                  </IconButton>
                )}
            </div>
            {data.originalSymptoms &&
              data.originalSymptoms !== data.symptoms && (
                <Collapse in={showOriginalSymptoms}>
                  <Typography variant="body2" color="text.secondary">
                    {data.originalSymptoms}
                  </Typography>
                </Collapse>
              )}
          </AccordionDetails>
        </Accordion>
      </Paper>

      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography
              className="cui_bold-500"
              component="span"
              data-cy="toc-detail-sub-accordion-root-cause-title"
            >
              {t("toc_details.description.root_cause")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails data-cy="toc-detail-sub-accordion-root-cause-value">
            <div className="toc_detail_description__title_row">
              <Typography component="span">
                {data.rootCause ? data.rootCause : "---"}
              </Typography>
              {data.originalRootCause &&
                data.originalRootCause !== data.rootCause && (
                  <IconButton
                    size="small"
                    onClick={handleToggleOriginalRootCause}
                    data-cy="toc-detail-original-root-cause-toggle"
                  >
                    <TranslateIcon fontSize="small" />
                  </IconButton>
                )}
            </div>
            {data.originalRootCause &&
              data.originalRootCause !== data.rootCause && (
                <Collapse in={showOriginalRootCause}>
                  <Typography variant="body2" color="text.secondary">
                    {data.originalRootCause}
                  </Typography>
                </Collapse>
              )}
          </AccordionDetails>
        </Accordion>
      </Paper>

      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography
              className="cui_bold-500"
              component="span"
              data-cy="toc-detail-sub-accordion-solution-title"
            >
              {t("toc_details.description.solution")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails data-cy="toc-detail-sub-accordion-solution-value">
            <div className="toc_detail_description__title_row">
              <Typography component="span">
                {data.solution ? data.solution : "---"}
              </Typography>
              {data.originalSolution &&
                data.originalSolution !== data.solution && (
                  <IconButton
                    size="small"
                    onClick={handleToggleOriginalSolution}
                    data-cy="toc-detail-original-solution-toggle"
                  >
                    <TranslateIcon fontSize="small" />
                  </IconButton>
                )}
            </div>
            {data.originalSolution &&
              data.originalSolution !== data.solution && (
                <Collapse in={showOriginalSolution}>
                  <Typography variant="body2" color="text.secondary">
                    {data.originalSolution}
                  </Typography>
                </Collapse>
              )}
          </AccordionDetails>
        </Accordion>
      </Paper>

      {data.thirdPartyJobDescription && (
        <Paper elevation={1}>
          <Accordion defaultExpanded>
            <AccordionSummary expandIcon={<ExpandMoreIcon />}>
              <Typography
                className="cui_bold-500"
                component="span"
                data-cy="toc-detail-sub-accordion-solution-title"
              >
                {t("toc_details.description.third_party_job_description")}
              </Typography>
            </AccordionSummary>
            <Divider />
            <AccordionDetails data-cy="toc-detail-sub-accordion-solution-value">
              {data.thirdPartyJobDescription}
            </AccordionDetails>
          </Accordion>
        </Paper>
      )}

      {data.thirdPartyHours && (
        <Paper elevation={1}>
          <Accordion defaultExpanded>
            <AccordionSummary expandIcon={<ExpandMoreIcon />}>
              <Typography
                className="cui_bold-500"
                component="span"
                data-cy="toc-detail-sub-accordion-solution-third-party-title"
              >
                {t("toc_details.description.third_party_hours")}
              </Typography>
            </AccordionSummary>
            <Divider />
            <AccordionDetails data-cy="toc-detail-sub-accordion-solution-value">
              {data.thirdPartyHours}
            </AccordionDetails>
          </Accordion>
        </Paper>
      )}

      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography
              className="cui_bold-500"
              component="span"
              data-cy="toc-detail-sub-accordion-contact-title"
            >
              {t("toc_details.contact.title")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails data-cy="toc-detail-sub-accordion-solution-value">
            <div className="toc_detail_description__contact_wrapper">
              <div className="toc_detail_description__contact_section_wrapper">
                <div data-cy="toc-detail-sub-accordion-main-contact-heading">
                  <Typography variant="subtitle2">
                    {t("toc_details.contact.main_contact")}
                  </Typography>
                </div>
                <div className="cui_w-100">
                  {data.mainContact ? (
                    <ButtonBase onClick={() => handleContact(data.mainContact)}>
                      <Chip
                        label={formatContact(data.mainContact)}
                        variant="outlined"
                        color="primary"
                        size="small"
                        data-cy="toc-detail-sub-accordion-main-contact-pill"
                      />
                    </ButtonBase>
                  ) : (
                    "---"
                  )}
                </div>
              </div>
              <div className="toc_detail_description__contact_section_wrapper">
                <div data-cy="toc-detail-sub-accordion-additional-contact-heading">
                  <Typography variant="subtitle2">
                    {t("toc_details.contact.other_contacts")}
                  </Typography>
                </div>
                <div className="toc_detail_description__contact_section_wrapper">
                  {data.contacts.length
                    ? data.contacts.map((contact) => {
                        return (
                          <ButtonBase onClick={() => handleContact(contact)}>
                            <Chip
                              label={formatContact(contact)}
                              variant="outlined"
                              color="primary"
                              size="small"
                              data-cy="toc-detail-sub-accordion-additional-contact-pill"
                            />
                          </ButtonBase>
                        );
                      })
                    : "---"}
                </div>
              </div>
            </div>
          </AccordionDetails>
        </Accordion>
      </Paper>

      {data.customerServiceRecords.length !== 0 && (
        <Paper elevation={1} id="csr-list">
          <Accordion defaultExpanded>
            <AccordionSummary expandIcon={<ExpandMoreIcon />}>
              <Typography
                className="cui_bold-500"
                component="span"
                data-cy="toc-detail-linked-csr-title"
              >
                {`${t("toc_details.csr.title")} (${
                  data.customerServiceRecords.length
                })`}
              </Typography>
            </AccordionSummary>
            <Divider />
            <AccordionDetails
              className="toc_detail_description__list"
              data-cy="toc-detail-linked-csr-value"
            >
              {data.customerServiceRecords.map((csr, idx) => (
                <div
                  className="toc_detail_description__card"
                  data-cy={`toc-detail-linked-csr-value-${idx}`}
                >
                  <div className="toc_detail_description__row">
                    <Box
                      className="cui_link"
                      onClick={() => handleCsrClick(`${csr.id}`)}
                      data-cy={`toc-detail-linked-csr-${idx}-id`}
                    >
                      {`#${csr.id}`}
                    </Box>
                    <div
                      className="toc_detail_description__csr_status"
                      data-cy={`toc-detail-linked-csr-${idx}-status`}
                    >
                      {CSR_FILTER_STATUS_MAP[csr.status]}
                    </div>
                    <Chip
                      className="toc_detail_description__csr_open"
                      label={
                        csr.closed
                          ? t("toc_details.csr.status.closed")
                          : t("toc_details.csr.status.open")
                      }
                      variant="outlined"
                      color="primary"
                      size="small"
                      data-cy={`toc-detail-linked-csr-${idx}-closed`}
                    />
                  </div>
                  <div
                    className="cui_one_line"
                    data-cy={`toc-detail-linked-csr-${idx}-title`}
                  >
                    {csr.title}
                  </div>
                </div>
              ))}
            </AccordionDetails>
          </Accordion>
        </Paper>
      )}

      {links.length !== 0 && (
        <Paper elevation={1}>
          <Accordion defaultExpanded>
            <AccordionSummary expandIcon={<ExpandMoreIcon />}>
              <Typography
                className="cui_bold-500"
                component="span"
                data-cy="toc-detail-links-title"
              >
                {t("toc_details.links.title")}
              </Typography>
            </AccordionSummary>
            <Divider />
            <AccordionDetails
              className="toc_detail_description__list"
              data-cy="toc-detail-links"
            >
              {links.map((link, idx) => (
                <div
                  className="toc_detail_description__card"
                  data-cy={`toc-detail-link-${idx}`}
                >
                  <div className="toc_detail_description__row">
                    <Box data-cy={`toc-detail-link-${idx}-id`}>
                      {`#${link.ref}`}
                    </Box>
                    <div
                      className="text_center"
                      data-cy={`toc-detail-link-${idx}-ref`}
                    >
                      {link.status}
                    </div>
                    <Chip
                      className="toc_detail_description__csr_open"
                      label={link.module}
                      variant="outlined"
                      color="primary"
                      size="small"
                      data-cy={`toc-detail-link-${idx}-module`}
                    />
                  </div>
                  <div
                    data-cy={`toc-detail-link-${idx}-description`}
                    // eslint-disable-next-line react/no-danger
                    dangerouslySetInnerHTML={{
                      __html: sanitize(link.description),
                    }}
                  />
                </div>
              ))}
            </AccordionDetails>
          </Accordion>
        </Paper>
      )}

      <FeatureComponent features={["FEATURE_TECHNICIAN_ON_CALL_EDIT"]}>
        <FloatingActionButton icon={<CreateIcon />} onClick={handleEdit} />
      </FeatureComponent>
    </div>
  );
}
