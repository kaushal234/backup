import React, { useEffect, useMemo } from "react";
import "./ManualDetails.css";
import { useParams } from "react-router";
import { useTranslation } from "react-i18next";
import {
  Paper,
  Accordion,
  AccordionSummary,
  Typography,
  Divider,
  AccordionDetails,
} from "@mui/material";
import dayjs from "dayjs";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { setBreadcrumbs } from "../../redux/slices/breadcrumbSlice";
import { DATE_FORMAT } from "../../constants/constants";
import { getManualById } from "../../api/getManualById";
import { setManualDetail } from "../../redux/slices/manualDetailSlice";
import DescriptionCard from "../../components/DescriptionCard/DescriptionCard";
import { convertToTitleCase } from "../../utils/utils";
import { IManualDocument } from "../../@type/IGetManualResponse";
import { IDocumentGroup } from "../../@type/IDocumentGroup";
import ManualDocumentGroupTable from "../../components/ManualDocumentGroupTable/ManualDocumentGroupTable";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { getEquipmentRecordById } from "../../api/getEquipmentRecordById";
import { setErDetail } from "../../redux/slices/erDetailSlice";
import { toastError } from "../../utils/api";

const pageBreadcrumbs = [
  { title: "breadcrumb.er.home", link: ROUTES.er.home },
  { title: "breadcrumb.er.details", link: ROUTES.er.details },
  { title: "breadcrumb.er.manual.home", link: "" },
];

const groupDocuments = (documents: Array<IManualDocument>) => {
  const result: IDocumentGroup = { manual: [], parts: [] };
  if (documents) {
    documents.forEach((document) => {
      if (document.category?.name) {
        const type = document.category.name.startsWith("Chapter")
          ? "manual"
          : "parts";
        const category = result[type].find(
          (currentCategory) => currentCategory.name === document.category?.name
        );
        if (category) {
          category.files.push(document);
        } else {
          result[type].push({
            name: document.category.name,
            files: [document],
          });
        }
      }
    });
  }
  result.manual.sort((a, b) => a.name.localeCompare(b.name));
  result.parts.sort((a, b) => a.name.localeCompare(b.name));
  return result;
};

function ManualDetails() {
  useDrawer(ROUTES.er.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const { erId, manualId } = useParams();
  const dispatch = useAppDispatch();
  const data = useAppSelector((state) => state.manualDetail.data);
  const erData = useAppSelector((state) => state.erDetail.data);

  const fetchManualDetail = async () => {
    const response = await getManualById({ id: manualId ?? "" });
    if (response.data) {
      dispatch(setManualDetail(response.data));
    } else {
      toastError(dispatch, response);
    }
  };

  const updateBreadcrumbs = () => {
    const newBreadcrumbs: Array<IBreadcrumb> = [
      { title: "breadcrumb.er.home", link: ROUTES.er.home },
      {
        title: "breadcrumb.er.details",
        link: `${ROUTES.er.details}/${erId}`,
        appendText: `(#${erData?.serialNumber ?? ""})`,
      },
      {
        title: "breadcrumb.er.manual.home",
        link: "",
        appendText: `(#${manualId})`,
      },
    ];
    dispatch(setBreadcrumbs(newBreadcrumbs));
  };

  const fetchErDetail = async () => {
    const response = await getEquipmentRecordById({ id: erId ?? "" });
    if (response.data) {
      dispatch(setErDetail(response.data));
    } else {
      toastError(dispatch, response);
    }
  };

  const fetchErAndManualDetail = async () => {
    dispatch(showMainLoader(true));
    const manualPromise = fetchManualDetail();
    const erPromise = fetchErDetail();
    await Promise.all([manualPromise, erPromise]);
    dispatch(showMainLoader(false));
  };

  useEffect(() => {
    updateBreadcrumbs();
  }, [erData]);

  useEffect(() => {
    fetchErAndManualDetail();
    return () => {
      dispatch(setManualDetail(null));
    };
  }, []);

  const formattedCreatedAtDate = data?.createdAt
    ? dayjs(data.createdAt).format(DATE_FORMAT)
    : "---";

  const formattedStatus = data?.status
    ? convertToTitleCase(data.status)
    : "---";

  const documentGroups = useMemo(() => {
    return groupDocuments(data?.documents ?? []);
  }, [data?.documents]);

  if (!data) return <div />;

  return (
    <div className="manual_details__wrapper">
      <div className="manual_details__row">
        <DescriptionCard
          title="manual_details.fields.id"
          value={data.id?.toString()}
          dataCy="manual-main-info-id"
        />
        <DescriptionCard
          title="manual_details.fields.legacy_id"
          value={data.legacyId?.toString()}
          dataCy="manual-main-info-legacy-id"
        />
      </div>
      <div className="manual_details__row">
        <DescriptionCard
          title="manual_details.fields.status"
          value={formattedStatus}
          dataCy="manual-main-info-status"
        />
        <DescriptionCard
          title="manual_details.fields.model"
          value={data.equipmentRecord?.model ?? "---"}
          dataCy="manual-main-info-model"
        />
      </div>
      <div className="manual_details__row">
        <DescriptionCard
          title="manual_details.fields.language"
          value={data.language}
          dataCy="manual-main-info-language"
        />
        <DescriptionCard
          title="manual_details.fields.created_at"
          value={formattedCreatedAtDate}
          dataCy="manual-main-info-created-at"
        />
      </div>
      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography
              className="cui_bold-500"
              component="span"
              data-cy="manual-accordion-description-title"
            >
              {t("manual_details.fields.description")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails data-cy="manual-accordion-description-value">
            {data.description}
          </AccordionDetails>
        </Accordion>
      </Paper>

      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography
              className="cui_bold-500"
              component="span"
              data-cy="manual-accordion-features-title"
            >
              {t("manual_details.fields.features")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails data-cy="manual-accordion-features-value">
            {data.features}
          </AccordionDetails>
        </Accordion>
      </Paper>

      {(!!documentGroups.manual.length || !!documentGroups.parts.length) && (
        <div className="manual_details__files_sections">
          <Typography
            className="cui_light_text "
            variant="h5"
            data-cy="manual-section-main-heading"
          >
            {t("manual_details.files.main_heading")}
          </Typography>

          {!!documentGroups.manual.length && (
            <div className="manual_details__files_section">
              <Typography
                className="cui_light_text"
                variant="h6"
                data-cy="manual-section-manual-heading"
              >
                {t("manual_details.files.section.manual")}
              </Typography>

              {documentGroups.manual.map((documentGroup) => (
                <ManualDocumentGroupTable
                  data={documentGroup}
                  key={documentGroup.name}
                  dataCy="manual-section-manual"
                />
              ))}
            </div>
          )}

          {!!documentGroups.parts.length && (
            <div className="manual_details__files_section">
              <Typography
                className="cui_light_text"
                variant="h6"
                data-cy="manual-section-parts-heading"
              >
                {t("manual_details.files.section.parts")}
              </Typography>

              {documentGroups.parts.map((documentGroup) => (
                <ManualDocumentGroupTable
                  data={documentGroup}
                  key={documentGroup.name}
                  dataCy="manual-section-parts"
                />
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  );
}

export default ManualDetails;
