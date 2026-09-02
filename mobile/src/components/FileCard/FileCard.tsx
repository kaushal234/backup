import React, { useEffect, useMemo, useState } from "react";
import "./FileCard.css";
import {
  Chip,
  Divider,
  IconButton,
  Paper,
  Skeleton,
  Typography,
} from "@mui/material";
import DescriptionIcon from "@mui/icons-material/Description";
import { useTranslation } from "react-i18next";
import EditIcon from "@mui/icons-material/Edit";
import DeleteIcon from "@mui/icons-material/Delete";
import FileDownloadIcon from "@mui/icons-material/FileDownload";
import { downloadFile, getFileNameFromPath } from "../../utils/utils";
import { IFileDownloadParams } from "../../@type/IFileDownloadParams";
import ImageViewer from "../ImageViewer/ImageViewer";
import { IFilePreview } from "../../@type/IFilePreview";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { useAppDispatch } from "../../hooks/hooks";

interface IProps {
  file: IFilePreview;
  fetchUrl?: () => Promise<string>;
  dataCy?: string;
  onEdit?: () => void;
  onDelete?: () => void;
}

export default function FileCard(props: IProps) {
  const { file: parentFile, fetchUrl, dataCy, onEdit, onDelete } = props;
  const { t } = useTranslation();
  const [file, setFile] = useState<IFilePreview>(parentFile);
  const isImage = file.mimeType.startsWith("image");
  const [isModalOpen, setIsModalOpen] = useState(false);
  const dispatch = useAppDispatch();

  const formattedFileName = useMemo(
    () => getFileNameFromPath(file.name),
    [file.name]
  );

  const handleOpen = () => {
    setIsModalOpen(true);
  };

  const handleClose = () => {
    setIsModalOpen(false);
  };

  const handleDownload = async () => {
    const params: IFileDownloadParams = {
      fileName: formattedFileName,
      url: file.url ?? "",
    };
    if (!params.url && fetchUrl) {
      const newUrl = await fetchUrl();
      params.url = newUrl;
      setFile((prev) => ({ ...prev, url: newUrl }));
    }
    if (!params.url) {
      dispatch(setToastMessage("common.error.internet_error"));
    }
    downloadFile(params);
  };

  const fetchImage = async () => {
    if (isImage && fetchUrl) {
      const newUrl = await fetchUrl();
      setFile((prev) => ({ ...prev, url: newUrl }));
    }
  };

  useEffect(() => {
    fetchImage();
  }, []);

  return (
    <>
      <ImageViewer
        url={file?.url ?? ""}
        isOpen={isModalOpen}
        onClose={handleClose}
        fileName={formattedFileName}
      />
      <Paper className="file_card__wrapper">
        <Chip
          className="file_card__chip"
          color="primary"
          size="small"
          label={t(
            file.chipText ? file.chipText : t("files_preview.regular_file")
          )}
          data-cy={`${dataCy}-chip`}
        />
        {isImage && (
          <div
            className="file_card__image_wrapper"
            data-cy={`${dataCy}-thumbnail`}
          >
            {file.url && (
              <IconButton
                className="file_card__image_button"
                onClick={handleOpen}
              >
                <img
                  src={file.url}
                  alt="toc-card-image"
                  data-cy={`${dataCy}-thumbnail-img`}
                />
              </IconButton>
            )}
            {!file.url && (
              <Skeleton variant="rectangular" width="100%" height={200} />
            )}
          </div>
        )}
        {!isImage && (
          <div
            className="file_card__file_wrapper"
            data-cy={`${dataCy}-thumbnail`}
          >
            <DescriptionIcon fontSize="large" />
          </div>
        )}
        <Divider />
        <div className="file_card__details">
          <div className="file_card__left_section">
            <div>
              <div className="cui_one_line file_card__card_title">
                <Typography
                  variant="caption"
                  gutterBottom
                  data-cy={`${dataCy}-file-type`}
                >
                  {t(file.chipText ?? t("files_preview.regular_file"))}
                </Typography>
              </div>
              <div className="cui_one_line">
                <Typography
                  variant="subtitle2"
                  gutterBottom
                  data-cy={`${dataCy}-file-name`}
                >
                  {`${formattedFileName.split("").join(" ")}`}
                </Typography>
              </div>
              <div className="cui_one_line file_card__description">
                <Typography
                  variant="caption"
                  gutterBottom
                  data-cy={`${dataCy}-file-description`}
                >
                  {`${(file.description ? file.description : "---")
                    .split("")
                    .join(" ")}`}
                </Typography>
              </div>
            </div>
          </div>
          <div className="file_card__right_section">
            {onEdit && (
              <IconButton onClick={onEdit} data-cy={`${dataCy}-action-edit`}>
                <EditIcon color="primary" />
              </IconButton>
            )}
            {onDelete && (
              <IconButton
                onClick={onDelete}
                data-cy={`${dataCy}-action-delete`}
              >
                <DeleteIcon color="primary" />
              </IconButton>
            )}
            <IconButton
              onClick={handleDownload}
              data-cy={`${dataCy}-action-download`}
            >
              <FileDownloadIcon color="primary" />
            </IconButton>
          </div>
        </div>
      </Paper>
    </>
  );
}
