import React, { useEffect } from "react";
import "./ErDetailDescription.css";
import {
  Accordion,
  AccordionDetails,
  AccordionSummary,
  Button,
  Divider,
  Paper,
  Typography,
} from "@mui/material";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import { useTranslation } from "react-i18next";
import dayjs from "dayjs";
import SendIcon from "@mui/icons-material/Send";
import { useNavigate } from "react-router";
import DescriptionCard from "../DescriptionCard/DescriptionCard";
import {
  DATE_FORMAT,
  TOC_CREATE_PARAMS,
  TOC_STATUS_FILTER_DEFAULT,
  TOC_STATUS_FILTER_OTHERS,
} from "../../constants/constants";
import { IEquipmentRecord } from "../../@type/IGetEquipmentRecordResponse";
import { ROUTES } from "../../constants/routes";
import { getAllTechnicianOnCall } from "../../api/getAllTechnicianOnCall";
import { formatTocFilters } from "../../utils/utils";
import { initialState, setTocFilters } from "../../redux/slices/tocFilterSlice";
import { useAppDispatch } from "../../hooks/hooks";
import { IDropdownItem } from "../../@type/IDropdownItem";

interface IProps {
  data: IEquipmentRecord;
}

export default function ErDetailDescription(props: IProps) {
  const { data } = props;
  const { t } = useTranslation();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();

  const serialNumber: IDropdownItem = {
    id: data["@id"],
    text: data.serialNumber ?? "",
  };

  const [linkedOpenToc, setLinkedOpenToc] = React.useState<string | null>(null);
  const [linkedClosedToc, setLinkedClosedToc] = React.useState<string | null>(
    null
  );

  const formattedCommissionedDate = data.dateCommissioned
    ? dayjs(data.dateCommissioned).format(DATE_FORMAT)
    : "---";

  const formattedShippedDate = data.dateShipped
    ? dayjs(data.dateShipped).format(DATE_FORMAT)
    : "---";

  const formattedGtDate = data.greenTagDate
    ? dayjs(data.greenTagDate).format(DATE_FORMAT)
    : "---";

  const handleCreateToc = () => {
    navigate(
      `${ROUTES.toc.create}?${TOC_CREATE_PARAMS.erSerialNumber}=${data.serialNumber}`
    );
  };

  const handleTocSearch = (status: Array<IDropdownItem>) => {
    if (status.length === 3 && Number(linkedOpenToc) === 0) return;
    if (status.length === 2 && Number(linkedClosedToc) === 0) return;
    dispatch(
      setTocFilters({
        ...initialState.filters,
        status,
        serialNumber,
      })
    );
    navigate(ROUTES.toc.home);
  };

  const fetchTocList = async () => {
    const openTocPromise = getAllTechnicianOnCall({
      ...formatTocFilters({
        ...initialState.filters,
        serialNumber,
        status: TOC_STATUS_FILTER_DEFAULT,
      }),
    });
    const closedTocPromise = getAllTechnicianOnCall({
      ...formatTocFilters({
        ...initialState.filters,
        serialNumber,
        status: TOC_STATUS_FILTER_OTHERS,
      }),
    });
    const [openTocResponse, closedTocResponse] = await Promise.all([
      openTocPromise,
      closedTocPromise,
    ]);
    if (openTocResponse.data) {
      setLinkedOpenToc(`${openTocResponse.data["hydra:member"].length}`);
    } else {
      setLinkedOpenToc("0");
    }
    if (closedTocResponse.data) {
      setLinkedClosedToc(`${closedTocResponse.data["hydra:member"].length}`);
    } else {
      setLinkedClosedToc("0");
    }
  };

  useEffect(() => {
    fetchTocList();
  }, []);

  return (
    <div className="er_detail_description__wrapper">
      <div className="er_detail_description__heading_wrapper">
        <Typography
          className="cui_light_text"
          variant="h5"
          gutterBottom
          data-cy="er-heading"
        >
          {t("er_details.heading")}
        </Typography>
        <div>
          <Button
            className="cui_button primary"
            variant="contained"
            size="small"
            endIcon={<SendIcon />}
            onClick={handleCreateToc}
            data-cy="create-toc-button"
          >
            {t("er_details.create_toc")}
          </Button>
        </div>
      </div>
      <div className="er_detail_description__row">
        <DescriptionCard
          title="er_details.description.model"
          value={data.model ?? "---"}
          dataCy="er-detail-main-info-model"
        />
        <DescriptionCard
          title="er_details.description.commissioned_at"
          value={formattedCommissionedDate}
          dataCy="er-detail-main-info-commissioned-date"
        />
      </div>
      <div className="er_detail_description__row">
        <DescriptionCard
          title="er_details.description.type"
          value={data.type ?? "---"}
          dataCy="er-detail-main-info-type"
        />
        <DescriptionCard
          title="er_details.description.airport"
          value={data.airport?.code ?? "---"}
          dataCy="er-detail-main-info-airport"
        />
      </div>
      <div className="er_detail_description__row">
        <DescriptionCard
          title="er_details.description.hour_meter"
          value={data.hourMeter?.toString() ?? "---"}
          dataCy="er-detail-main-info-hour-meter"
        />
        <DescriptionCard
          title="er_details.description.state"
          value={data.state}
          dataCy="er-detail-main-info-state"
        />
      </div>
      <div className="er_detail_description__row">
        <DescriptionCard
          title="er_details.description.linked_open_toc"
          value={linkedOpenToc ?? t("common.loading")}
          dataCy="er-detail-main-info-open-toc"
          className={
            Number(linkedOpenToc) ? "" : "er_detail_description__no_link"
          }
          onClick={() => handleTocSearch(TOC_STATUS_FILTER_DEFAULT)}
        />
        <DescriptionCard
          title="er_details.description.linked_closed_toc"
          value={linkedClosedToc ?? t("common.loading")}
          dataCy="er-detail-main-info-closed-toc"
          className={
            Number(linkedClosedToc) ? "" : "er_detail_description__no_link"
          }
          onClick={() => handleTocSearch(TOC_STATUS_FILTER_OTHERS)}
        />
      </div>

      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography className="cui_bold-500" component="span">
              {t("er_details.description.additional_info.title")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails>
            <div className="er_detail_description__info_row_wrapper">
              <div className="er_detail_description__info_row">
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-serial-number-title"
                >
                  {t("er_details.description.additional_info.serial_number")}
                </div>
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-serial-number-value"
                >
                  {data.serialNumber}
                </div>
              </div>
              <div className="er_detail_description__info_row">
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-status-title"
                >
                  {t("er_details.description.additional_info.status")}
                </div>
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-status-value"
                >
                  {data.status ?? "---"}
                </div>
              </div>
              <div className="er_detail_description__info_row">
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-emission-rating-title"
                >
                  {t("er_details.description.additional_info.emission_rating")}
                </div>
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-emission-rating-value"
                >
                  {data.emissionRating?.name ?? "---"}
                </div>
              </div>
              <div className="er_detail_description__info_row">
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-manufacturing-location-title"
                >
                  {t(
                    "er_details.description.additional_info.manufacturing_location"
                  )}
                </div>
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-manufacturing-location-value"
                >
                  {data.manufacturerLocation?.name ?? "---"}
                </div>
              </div>
              <div className="er_detail_description__info_row">
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-ship-date-title"
                >
                  {t("er_details.description.additional_info.ship_date")}
                </div>
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-ship-date-value"
                >
                  {formattedShippedDate}
                </div>
              </div>
              <div className="er_detail_description__info_row">
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-gt-date-title"
                >
                  {t("er_details.description.additional_info.gt_date")}
                </div>
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-gt-date-value"
                >
                  {formattedGtDate}
                </div>
              </div>
              <div className="er_detail_description__info_row">
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-buyer-title"
                >
                  {t("er_details.description.additional_info.buyer")}
                </div>
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-buyer-value"
                >
                  {data.buyer?.name ?? "---"}
                </div>
              </div>
              <div className="er_detail_description__info_row">
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-end-user-title"
                >
                  {t("er_details.description.additional_info.end_user")}
                </div>
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-end-user-value"
                >
                  {data.endUser?.name ?? "---"}
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
              data-cy="er-detail-sub-accordion-installed-options-title"
            >
              {t("er_details.description.installed_options")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails data-cy="er-detail-sub-accordion-installed-options-value">
            {data.optionsDescription ?? "---"}
          </AccordionDetails>
        </Accordion>
      </Paper>

      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography
              className="cui_bold-500"
              component="span"
              data-cy="er-detail-sub-accordion-dimensions-heading"
            >
              {t("er_details.description.dimensions.title")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails>
            <div className="er_detail_description__info_row_wrapper">
              <div className="er_detail_description__info_row">
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-length-title"
                >
                  {t("er_details.description.dimensions.length")}
                </div>
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-length-value"
                >
                  {data.length ?? "---"}
                </div>
              </div>
              <div className="er_detail_description__info_row">
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-width-title"
                >
                  {t("er_details.description.dimensions.width")}
                </div>
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-width-value"
                >
                  {data.width ?? "---"}
                </div>
              </div>
              <div className="er_detail_description__info_row">
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-height-title"
                >
                  {t("er_details.description.dimensions.height")}
                </div>
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-height-value"
                >
                  {data.height ?? "---"}
                </div>
              </div>
              <div className="er_detail_description__info_row">
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-weight-title"
                >
                  {t("er_details.description.dimensions.weight")}
                </div>
                <div
                  className="er_detail_description__info_cell"
                  data-cy="er-detail-sub-accordion-weight-value"
                >
                  {data.weight ?? "---"}
                </div>
              </div>
            </div>
          </AccordionDetails>
        </Accordion>
      </Paper>
    </div>
  );
}
