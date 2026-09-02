import React, { useState } from "react";
import "./FileChip.css";
import { Avatar, Box, Typography } from "@mui/material";
import DescriptionIcon from "@mui/icons-material/Description";
import DownloadIcon from "@mui/icons-material/Download";
import RemoveRedEyeIcon from "@mui/icons-material/RemoveRedEye";
import ImageViewer from "../ImageViewer/ImageViewer";
import { IFile } from "../../types/IFile";
import { downloadFile, getFileNameFromPath } from "../../utils/utils";

interface IProps {
  file: IFile;
}

export default function FileChip(props: IProps) {
  const { file } = props;
  const isImage = file.mimeType.startsWith("image");
  const [showPerviewDialog, setShowPreviewDialog] = useState(false);

  const fileName = getFileNameFromPath(file.filePath);

  const handleClick = async () => {
    if (isImage) {
      setShowPreviewDialog(true);
    } else {
      downloadFile({ fileName: file.filePath, url: file.filePath });
    }
  };

  const handleView = () => {
    setShowPreviewDialog(true);
  };

  const handleDownload = () => {
    downloadFile({ fileName, url: file.filePath });
  };

  return (
    <>
      <ImageViewer
        isOpen={showPerviewDialog}
        onClose={() => {
          setShowPreviewDialog(false);
        }}
        url={file.filePath}
        fileName={fileName}
      />
      <Box className="file_chip__wrapper">
        <Box onClick={handleClick}>
          {!isImage && (
            <Avatar className="file_chip__avatar">
              <DescriptionIcon />
            </Avatar>
          )}
          {isImage && (
            <Avatar className="file_chip__avatar file_chip__avatar--no_border">
              <img src={file.filePath} alt="file-chip" />
            </Avatar>
          )}
        </Box>
        <div className="file_chip__info_wrapper">
          <Typography className="cui_one_line" variant="body2" gutterBottom>
            {fileName}
          </Typography>
        </div>
        <Box
          className="file_chip__action_icon_wrapper"
          onClick={isImage ? handleView : handleDownload}
        >
          {isImage && <RemoveRedEyeIcon />}
          {!isImage && <DownloadIcon />}
        </Box>
      </Box>
    </>
  );
}
