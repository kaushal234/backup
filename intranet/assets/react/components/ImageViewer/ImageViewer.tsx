import React from "react";
import Dialog from "@mui/material/Dialog";
import DownloadForOfflineOutlinedIcon from "@mui/icons-material/DownloadForOfflineOutlined";
import "./ImageViewer.css";
import { IconButton } from "@mui/material";
import { downloadFile } from "../../utils/utils";

interface IProps {
  url: string;
  isOpen: boolean;
  onClose: () => void;
  fileName: string;
}
export default function ImageViewer(props: IProps) {
  const { url, isOpen, onClose, fileName } = props;

  const handleDownload = () => {
    downloadFile({
      fileName,
      url,
    });
  };

  if (!url) {
    return null;
  }

  return (
    <Dialog closeAfterTransition={false} onClose={onClose} open={isOpen}>
      <img src={url} alt="zoomed-image" data-cy="image-viewer-image" />
      <IconButton
        className="image_viewer__icon_wrapper"
        onClick={handleDownload}
        data-cy="image-viewer-action"
      >
        <DownloadForOfflineOutlinedIcon />
      </IconButton>
    </Dialog>
  );
}
