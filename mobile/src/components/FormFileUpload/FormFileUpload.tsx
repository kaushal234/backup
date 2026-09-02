import React from "react";
import { useTranslation } from "react-i18next";
import {
  Button,
  FormControl,
  FormHelperText,
  FormLabel,
  styled,
} from "@mui/material";
import CloudUploadIcon from "@mui/icons-material/CloudUpload";
import { mapFileListToFileArray } from "../../utils/utils";
import { ACCEPT_FILES, MAX_FILE_SIZE } from "../../constants/constants";
import { useAppDispatch } from "../../hooks/hooks";
import { setToastMessageWithParams } from "../../redux/slices/toastSlice";
import "./FormFileUpload.css";
import { IUploadFileType } from "../../@type/IUploadFileType";
import FileChip from "../FileChip/FileChip";
import { IFileWithDescription } from "../../@type/IFileWithDescription";

const VisuallyHiddenInput = styled("input")({
  clip: "rect(0 0 0 0)",
  clipPath: "inset(50%)",
  height: 1,
  overflow: "hidden",
  position: "absolute",
  bottom: 0,
  left: 0,
  whiteSpace: "nowrap",
  width: 1,
});

interface IProps {
  label?: string;
  className?: string;
  value: Array<IFileWithDescription> | null;
  onChange: (value: Array<IFileWithDescription> | null) => void;
  maxSize?: number;
  accept: IUploadFileType;
  error?: boolean;
  helperText?: string;
  onBlur?: () => void;
  allowMultipleFiles?: boolean;
  dataCy?: string;
}

function FormFileUpload(props: IProps) {
  const {
    label,
    className,
    maxSize = MAX_FILE_SIZE,
    onChange,
    value,
    accept,
    onBlur,
    error,
    helperText,
    allowMultipleFiles,
    dataCy,
  } = props;
  const { t } = useTranslation();
  const dispatch = useAppDispatch();

  const handleDelete = (index: number) => {
    const newValue = (value ?? []).filter((currentFile, idx) => idx !== index);
    onChange(newValue.length ? newValue : null);
  };

  const handleDescriptionChange = (description: string, idx: number) => {
    const newValue = [...(value ?? [])];
    newValue[idx].description = description;
    onChange(newValue);
  };

  const handleFileNameChange = (fileName: string, idx: number) => {
    const newValue = [...(value ?? [])];
    newValue[idx].file = new File([newValue[idx].file], fileName, {
      type: newValue[idx].file.type,
    });
    onChange(newValue);
  };

  return (
    <FormControl>
      {label && (
        <FormLabel className="cui_label" data-cy={`${dataCy}-title`}>
          {t(label)}
        </FormLabel>
      )}
      <div
        className={`form_file_upload__wrapper ${
          value && "form_file_upload__wrapper-files"
        }`}
      >
        <div className="form_file_upload__files_wrapper">
          {value &&
            value.map((fileData, idx) => (
              <FileChip
                // eslint-disable-next-line react/no-array-index-key
                key={`${fileData.file.name}-${idx}`}
                className="form_file_upload__file_chip"
                fileName={fileData.file.name}
                onActionIconClick={() => handleDelete(idx)}
                mimeType={fileData.file.type}
                showCrossActionIcon
                defaultFileSrc={URL.createObjectURL(fileData.file)}
                onDescriptionChange={(description) =>
                  handleDescriptionChange(description, idx)
                }
                onFileNameChange={(fileName) =>
                  handleFileNameChange(fileName, idx)
                }
                dataCy={`${dataCy}-value-${idx}`}
              />
            ))}
        </div>
        <Button
          className={`${className} cui_button`}
          component="label"
          role={undefined}
          variant="contained"
          tabIndex={-1}
          startIcon={<CloudUploadIcon />}
          data-cy={`${dataCy}-upload-button`}
        >
          {ACCEPT_FILES[accept] === ACCEPT_FILES.image
            ? t("common.upload_image")
            : t("common.upload_file")}
          <VisuallyHiddenInput
            type="file"
            accept={ACCEPT_FILES[accept]}
            onChange={(event) => {
              onBlur?.();
              const files = mapFileListToFileArray(event.target.files);
              let hasError = false;
              (files ?? []).forEach((file) => {
                if (file.size > maxSize) {
                  hasError = true;
                }
              });
              if (hasError) {
                dispatch(
                  setToastMessageWithParams({
                    toastMessage: "common.file_size_error",
                    params: { size: maxSize / 1024 / 1024 },
                  })
                );
                onChange(null);
              } else {
                onChange(
                  files
                    ? files.map((file) => ({ file, description: "" }))
                    : null
                );
              }
            }}
            multiple={allowMultipleFiles}
          />
        </Button>
        <FormHelperText error={error} data-cy={`${dataCy}-error`}>
          {helperText}
        </FormHelperText>
      </div>
    </FormControl>
  );
}

export default FormFileUpload;
