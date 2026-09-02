import React, { useState } from "react";
import "./DetailFiles.css";
import { useTranslation } from "react-i18next";
import { Avatar, IconButton, Typography } from "@mui/material";
import AddIcon from "@mui/icons-material/Add";
import { IFilePreview } from "../../@type/IFilePreview";
import FileCard from "../FileCard/FileCard";
import FileUploadPopUp from "../FileUploadPopUp/FileUploadPopUp";
import { IFileUploadPopUpSubmitParams } from "../../@type/IFileUploadPopUpSubmitParams";

interface IProps {
  files: Array<IFilePreview>;
  fetchUrl?: (index: number) => Promise<string>;
  onFileUpload: (data: IFileUploadPopUpSubmitParams) => void;
  hasMainFile?: boolean;
  onDelete?: (file: IFilePreview) => void;
}

export default function DetailFiles(props: IProps) {
  const { files, fetchUrl, onFileUpload, hasMainFile, onDelete } = props;
  const { t } = useTranslation();
  const [showFilePopUp, setShowFilePopUp] = useState(false);
  const [allowMainFileUpload, setAllowMainFilesUpload] = useState(!hasMainFile);
  const [allowOtherFilesUpload, setAllowOtherFilesUpload] = useState(true);

  const toggleAddFilePopUp = () => {
    setAllowMainFilesUpload(!hasMainFile);
    setAllowOtherFilesUpload(true);
    setShowFilePopUp((prev) => !prev);
  };

  const handlePopUpClose = () => {
    setShowFilePopUp(false);
  };

  const handlePopUpSubmit = (data: IFileUploadPopUpSubmitParams) => {
    setShowFilePopUp(false);
    onFileUpload(data);
  };

  const handleFetchUrl = (index: number) => {
    if (fetchUrl) {
      return fetchUrl(index);
    }
    return Promise.resolve("");
  };

  const handleEditMainFile = () => {
    setAllowMainFilesUpload(true);
    setAllowOtherFilesUpload(false);
    setShowFilePopUp(true);
  };

  return (
    <>
      <FileUploadPopUp
        isOpen={showFilePopUp}
        onClose={handlePopUpClose}
        onSubmit={handlePopUpSubmit}
        allowMainFileUpload={allowMainFileUpload}
        allowOtherFilesUpload={allowOtherFilesUpload}
      />
      <div className="detail_files__chat_heading_wrapper">
        <Typography
          className="cui_light_text"
          variant="h5"
          gutterBottom
          data-cy="detail-files-heading"
        >
          {t("files_preview.sub_heading")}
        </Typography>
        <Avatar className="detail_files__add_file" color="primary">
          <IconButton
            onClick={toggleAddFilePopUp}
            data-cy="detail-files-add-file"
          >
            <AddIcon />
          </IconButton>
        </Avatar>
      </div>
      <div className="detail_files__wrapper">
        {!files.length && (
          <div className="detail_files__no_files">
            <Typography
              variant="body1"
              gutterBottom
              data-cy="detail-files-no-content"
            >
              {t("files_preview.no_files")}
            </Typography>
          </div>
        )}
        {files.length !== 0 &&
          files.map((file, idx) => (
            <FileCard
              key={file.name}
              file={file}
              fetchUrl={() => handleFetchUrl(idx)}
              dataCy={`detail-files-${idx}`}
              onDelete={
                file.additionalInfo?.type === "TocFile"
                  ? () => onDelete?.(file)
                  : undefined
              }
              onEdit={
                file.additionalInfo?.type === "TocMainFile"
                  ? handleEditMainFile
                  : undefined
              }
            />
          ))}
      </div>
    </>
  );
}
