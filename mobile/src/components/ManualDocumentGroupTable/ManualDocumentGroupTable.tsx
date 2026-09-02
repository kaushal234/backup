import React from "react";
import {
  Paper,
  Accordion,
  AccordionSummary,
  Typography,
  Divider,
  AccordionDetails,
  IconButton,
  Avatar,
} from "@mui/material";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import "./ManualDocumentGroupTable.css";
import DownloadIcon from "@mui/icons-material/Download";
import RemoveRedEyeIcon from "@mui/icons-material/RemoveRedEye";
import { useNavigate, useParams } from "react-router";
import { IDocumentCategory } from "../../@type/IDocumentCategory";
import { IManualDocument } from "../../@type/IGetManualResponse";
import { getManualDocumentPdfById } from "../../api/getManualDocumentPdfById";
import { downloadFile } from "../../utils/utils";
import { ROUTES } from "../../constants/routes";
import SimpleTable from "../SimpleTable/SimpleTable";
import { useAppDispatch } from "../../hooks/hooks";
import { toastError } from "../../utils/api";

interface IProps {
  data: IDocumentCategory;
  dataCy?: string;
}

function ManualDocumentGroupTable(props: IProps) {
  const { data, dataCy } = props;
  const navigate = useNavigate();
  const { erId, manualId } = useParams();
  const dispatch = useAppDispatch();

  const handleDownload = async (file: IManualDocument) => {
    const response = await getManualDocumentPdfById({
      manualDocumentId: file.id.toString(),
      fileId: file.document?.id.toString() ?? "",
      extension: file.document?.extension ?? "",
      documentId: file?.document?.id?.toString() ?? "",
    });
    if (response.data) {
      downloadFile({
        fileName: file.document?.filePath ?? "",
        url: response.data.url ?? "",
      });
    } else {
      toastError(dispatch, response);
    }
  };

  const handleView = (file: IManualDocument) => {
    navigate(
      `${ROUTES.er.details}/${erId}/${ROUTES.er.manual.home}/${manualId}/${ROUTES.er.manual.document}/${file.id}`
    );
  };

  return (
    <Paper elevation={1}>
      <Accordion>
        <AccordionSummary expandIcon={<ExpandMoreIcon />}>
          <Typography
            className="cui_bold-500"
            component="span"
            data-cy={`${dataCy}-accordion-title`}
          >
            {data.name}
          </Typography>
        </AccordionSummary>
        <Divider />
        <AccordionDetails>
          <div className="manual_document_group_table__files_wrapper">
            <SimpleTable
              dataCy={`${dataCy}-table`}
              isOneLiner
              headers={[
                { value: "manual_details.files.fields.actions" },
                { value: "manual_details.files.fields.factory_number" },
                { value: "manual_details.files.fields.revision" },
                {
                  value: "manual_details.files.fields.other_description",
                  minWidth: "300px",
                },
                { value: "manual_details.files.fields.position" },
                { value: "manual_details.files.fields.document_number" },
                { value: "manual_details.files.fields.type" },
                { value: "manual_details.files.fields.category" },
              ]}
              rows={data.files.map((file, idx) => [
                <div className="manual_document_group_table__actions">
                  <IconButton
                    onClick={() => handleView(file)}
                    data-cy={`${dataCy}-table-row-view-${idx}`}
                  >
                    <Avatar>
                      <RemoveRedEyeIcon />
                    </Avatar>
                  </IconButton>
                  <IconButton
                    onClick={() => handleDownload(file)}
                    data-cy={`${dataCy}-table-row-download-${idx}`}
                  >
                    <Avatar>
                      <DownloadIcon />
                    </Avatar>
                  </IconButton>
                </div>,
                file.factoryNumber,
                file.revision,
                file.otherDescription,
                file.position,
                file?.id,
                file.type,
                file.category?.name,
              ])}
            />
          </div>
        </AccordionDetails>
      </Accordion>
    </Paper>
  );
}

export default ManualDocumentGroupTable;
