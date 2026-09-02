import React, { useEffect } from "react";
import "./ManualDocument.css";
import { useParams } from "react-router";
import { useTranslation } from "react-i18next";
import {
  Paper,
  Accordion,
  AccordionSummary,
  Typography,
  Divider,
  AccordionDetails,
  Button,
} from "@mui/material";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import CheckIcon from "@mui/icons-material/Check";
import ClearIcon from "@mui/icons-material/Clear";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { setBreadcrumbs } from "../../redux/slices/breadcrumbSlice";
import DescriptionCard from "../../components/DescriptionCard/DescriptionCard";
import { getManualDocumentById } from "../../api/getManualDocumentById";
import { setManualDocumentDetail } from "../../redux/slices/manualDocumentDetailSlice";
import { getManualDocumentPdfById } from "../../api/getManualDocumentPdfById";
import { downloadFile } from "../../utils/utils";
import SimpleTable from "../../components/SimpleTable/SimpleTable";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { getEquipmentRecordById } from "../../api/getEquipmentRecordById";
import { setErDetail } from "../../redux/slices/erDetailSlice";
import { toastError } from "../../utils/api";

function ManualDocument() {
  useDrawer(ROUTES.er.home);
  useBreadcrumbs([]);
  const { t } = useTranslation();
  const { erId, manualId, manualDocumentId } = useParams();
  const dispatch = useAppDispatch();
  const data = useAppSelector((state) => state.manualDocumentDetail.data);
  const erData = useAppSelector((state) => state.erDetail.data);

  const fetchManualDetail = async () => {
    const response = await getManualDocumentById({
      id: manualDocumentId ?? "",
    });
    if (response.data) {
      dispatch(setManualDocumentDetail(response.data));
    } else {
      toastError(dispatch, response);
    }
  };

  const handleDownload = async () => {
    const response = await getManualDocumentPdfById({
      manualDocumentId: data?.id?.toString() ?? "",
      fileId: data?.document?.id?.toString() ?? "",
      extension: data?.document?.extension ?? "",
      documentId: data?.document?.id?.toString() ?? "",
    });
    if (response.data) {
      downloadFile({
        fileName: data?.document?.filePath ?? "",
        url: response.data.url ?? "",
      });
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
        link: `${ROUTES.er.details}/${erId}/${ROUTES.er.manual.home}/${manualId}`,
        appendText: `(#${manualId})`,
      },
      {
        title: "breadcrumb.er.manual.document",
        link: "",
        appendText: `(#${manualDocumentId})`,
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
      dispatch(setManualDocumentDetail(null));
      dispatch(setErDetail(null));
    };
  }, []);

  if (!data) return <div />;

  return (
    <div className="manual_document__wrapper">
      <div className="manual_document__row">
        <DescriptionCard
          title="manual_document.fields.position"
          value={data.position.toString()}
          dataCy="manual-document-main-info-position"
        />
        <DescriptionCard
          title="manual_document.fields.document_number"
          value={data?.id.toString()}
          dataCy="manual-document-main-info-document-number"
        />
      </div>
      <div className="manual_document__row">
        <DescriptionCard
          title="manual_document.fields.factory_number"
          value={data.factoryNumber}
          dataCy="manual-document-main-info-factory-number"
        />
        <DescriptionCard
          title="manual_document.fields.revision"
          value={data.revision}
          dataCy="manual-document-main-info-revision"
        />
      </div>
      <div className="manual_document__row">
        <DescriptionCard
          title="manual_document.fields.type"
          value={data.type}
          dataCy="manual-document-main-info-type"
        />
        <DescriptionCard
          title="manual_document.fields.category"
          value={data.category?.name}
          dataCy="manual-document-main-info-category"
        />
      </div>
      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography
              className="cui_bold-500"
              component="span"
              data-cy="manual-document-accordion-description-title"
            >
              {t("manual_document.fields.description")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails data-cy="manual-document-accordion-description-value">
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
              data-cy="manual-document-accordion-other-description-title"
            >
              {t("manual_document.fields.other_description")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails data-cy="manual-document-accordion-other-description-value">
            {data.otherDescription}
          </AccordionDetails>
        </Accordion>
      </Paper>

      <div className="manual_document__actions">
        <Button
          variant="outlined"
          onClick={handleDownload}
          data-cy="manual-document-download"
        >
          {t("manual_document.actions.download")}
        </Button>
      </div>

      <Paper elevation={1}>
        <Accordion defaultExpanded>
          <AccordionSummary expandIcon={<ExpandMoreIcon />}>
            <Typography
              className="cui_bold-500"
              component="span"
              data-cy="manual-document-accordion-parts-list-title"
            >
              {t("manual_document.parts_list.title")}
            </Typography>
          </AccordionSummary>
          <Divider />
          <AccordionDetails>
            <div>
              {!data.parts.length && (
                <div>{t("manual_document.parts_list.no_items")}</div>
              )}

              {!!data.parts.length && (
                <SimpleTable
                  dataCy="manual-document-table"
                  headers={[
                    { value: "manual_document.parts_list.fields.position" },
                    { value: "manual_document.parts_list.fields.part_number" },
                    { value: "manual_document.parts_list.fields.quantity" },
                    { value: "manual_document.parts_list.fields.unit" },
                    {
                      value:
                        "manual_document.parts_list.fields.other_description",
                      minWidth: "300px",
                    },
                    { value: "manual_document.parts_list.fields.preventive" },
                    { value: "manual_document.parts_list.fields.maintenance" },
                    { value: "manual_document.parts_list.fields.overhaul" },
                    { value: "manual_document.parts_list.fields.critical" },
                  ]}
                  rows={data.parts.map((part) => [
                    part.position,
                    part.partNumber,
                    part.quantity,
                    part.unitOfMeasure,
                    part.otherDescription,
                    part.preventive ? (
                      <CheckIcon className="cui_success" />
                    ) : (
                      <ClearIcon className="cui_error" />
                    ),
                    part.maintenance ? (
                      <CheckIcon className="cui_success" />
                    ) : (
                      <ClearIcon className="cui_error" />
                    ),
                    part.overhaul ? (
                      <CheckIcon className="cui_success" />
                    ) : (
                      <ClearIcon className="cui_error" />
                    ),
                    part.critical ? (
                      <CheckIcon className="cui_success" />
                    ) : (
                      <ClearIcon className="cui_error" />
                    ),
                  ])}
                />
              )}
            </div>
          </AccordionDetails>
        </Accordion>
      </Paper>
    </div>
  );
}

export default ManualDocument;
