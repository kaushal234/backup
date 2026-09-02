import React, { useEffect, useState } from "react";
import "./FileChip.css";
import { Avatar, Box, Skeleton, Typography } from "@mui/material";
import DescriptionIcon from "@mui/icons-material/Description";
import CloseIcon from "@mui/icons-material/Close";
import DownloadIcon from "@mui/icons-material/Download";
import RemoveRedEyeIcon from "@mui/icons-material/RemoveRedEye";
import { useTranslation } from "react-i18next";
import ImageViewer from "../ImageViewer/ImageViewer";
import { cleanFilename, downloadFile } from "../../utils/utils";
import FormField from "../FormField/FormField";
import { useFormField } from "../../hooks/useFormField";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { useAppDispatch } from "../../hooks/hooks";

interface IProps {
  fileName: string;
  defaultFileSrc?: string;
  showCrossActionIcon?: boolean;
  mimeType: string;
  fetchSrc?: (isFirstFetch?: boolean) => Promise<string>;
  className?: string;
  onActionIconClick?: () => void;
  onFileNameChange?: (value: string) => void;
  onDescriptionChange?: (value: string) => void;
  dataCy?: string;
}

export default function FileChip(props: IProps) {
  const {
    fileName: defaultFileName,
    defaultFileSrc = "",
    showCrossActionIcon,
    mimeType,
    fetchSrc = () => Promise.resolve(""),
    className,
    onActionIconClick = () => {
      /* empty on purpose */
    },
    onDescriptionChange,
    onFileNameChange,
    dataCy,
  } = props;
  const { t } = useTranslation();
  const dispatch = useAppDispatch();
  const [fileSrc, setFileSrc] = useState(defaultFileSrc);
  const isImage = mimeType.startsWith("image");
  const [showPerviewDialog, setShowPreviewDialog] = useState(false);

  const fileName = useFormField({
    defaultValue: cleanFilename(defaultFileName),
    isFileName: true,
  });

  const description = useFormField({ defaultValue: "" });

  const handleActionClick = (
    event: React.MouseEvent<HTMLDivElement, MouseEvent>
  ) => {
    if (showCrossActionIcon) {
      event.stopPropagation();
      onActionIconClick();
    }
  };

  const handleFetchSrc = async (isFirstFetch?: boolean) => {
    const response = await fetchSrc(isFirstFetch);
    if (response) {
      setFileSrc(response);
      return response;
    }
    return "";
  };

  const handleClick = async () => {
    if (isImage) {
      if (fileSrc) {
        setShowPreviewDialog(true);
      } else {
        dispatch(setToastMessage("common.error.internet_error"));
      }
    } else {
      let newFileSrc = fileSrc;
      if (!fileSrc) {
        const response = await handleFetchSrc();
        if (response) {
          newFileSrc = response;
        }
      }
      downloadFile({ fileName: fileName.value, url: newFileSrc });
    }
  };

  useEffect(() => {
    if (isImage && !fileSrc) {
      handleFetchSrc(true);
    }
  }, []);

  useEffect(() => {
    if (onDescriptionChange) {
      onDescriptionChange(description.value);
    }
  }, [description.value]);

  return (
    <>
      <ImageViewer
        isOpen={showPerviewDialog}
        onClose={() => {
          setShowPreviewDialog(false);
        }}
        url={fileSrc}
        fileName={fileName.value}
      />
      <Box className={`file_chip__wrapper ${className}`}>
        <Box onClick={handleClick} data-cy={`${dataCy}-thumbnail`}>
          {!isImage && (
            <Avatar className="file_chip__avatar">
              <DescriptionIcon />
            </Avatar>
          )}
          {isImage && (
            <Avatar className="file_chip__avatar file_chip__avatar--no_border">
              {!fileSrc && <Skeleton className="file_chip__skeleton" />}
              {fileSrc && (
                <img
                  src={fileSrc}
                  alt="file-chip"
                  data-cy={`${dataCy}-thumbnail-img`}
                />
              )}
            </Avatar>
          )}
        </Box>
        <div className="file_chip__info_wrapper">
          {!onFileNameChange && (
            <Typography
              className="cui_one_line"
              variant="body2"
              gutterBottom
              data-cy={`${dataCy}-file-name-text`}
            >
              {`${fileName.value.split("").join(" ")}`}
            </Typography>
          )}
          {onFileNameChange && (
            <div className="file_chip__field_wrapper">
              <Typography variant="body2">
                {`${t("files_preview.popup.file_name.title")} *`}
              </Typography>
              <FormField
                className="file_chip__input"
                placeholder="files_preview.popup.file_name.placeholder"
                {...fileName.fieldProps}
                dataCy={`${dataCy}-file-name`}
                onBlur={() => {
                  const newFileName = cleanFilename(
                    fileName.value || "unnamed-file"
                  );
                  fileName.helper.setValue(newFileName);
                  onFileNameChange(newFileName);
                }}
              />
            </div>
          )}
          {onDescriptionChange && (
            <div className="file_chip__field_wrapper">
              <Typography variant="body2">
                {t("files_preview.popup.description.title")}
              </Typography>
              <FormField
                className="file_chip__input"
                placeholder="files_preview.popup.description.placeholder"
                {...description.fieldProps}
                dataCy={`${dataCy}-description`}
              />
            </div>
          )}
        </div>
        <Box
          className="file_chip__action_icon_wrapper"
          onClick={showCrossActionIcon ? handleActionClick : handleClick}
          data-cy={`${dataCy}-action-icon`}
        >
          {showCrossActionIcon && <CloseIcon />}
          {!showCrossActionIcon && isImage && <RemoveRedEyeIcon />}
          {!showCrossActionIcon && !isImage && <DownloadIcon />}
        </Box>
      </Box>
    </>
  );
}
