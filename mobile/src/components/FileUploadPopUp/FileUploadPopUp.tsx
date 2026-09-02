import React from "react";
import "./FileUploadPopUp.css";
import { Button, Dialog, Typography } from "@mui/material";
import { t } from "i18next";
import FormFileUpload from "../FormFileUpload/FormFileUpload";
import { useFormFileUpload } from "../../hooks/useFormFileUpload";
import { IFileWithDescription } from "../../@type/IFileWithDescription";
import { IFileUploadPopUpSubmitParams } from "../../@type/IFileUploadPopUpSubmitParams";
import { useFormValidator } from "../../hooks/useFormValidator";

const validateMainFile = (
  mainFile: Array<IFileWithDescription> | null,
  files: Array<IFileWithDescription> | null,
  allowMainFileUpload: boolean
) => {
  if (!allowMainFileUpload) return "";
  if (mainFile?.length || files?.length) return "";
  return "files_preview.popup.file.error";
};

const validateFile = (
  files: Array<IFileWithDescription> | null,
  allowMainFileUpload: boolean
) => {
  if (files?.length || allowMainFileUpload) return "";
  return "files_preview.popup.file.error";
};

interface IProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (data: IFileUploadPopUpSubmitParams) => void;
  allowMainFileUpload?: boolean;
  allowOtherFilesUpload?: boolean;
}

export default function FileUploadPopUp(props: IProps) {
  const {
    isOpen,
    onClose,
    onSubmit,
    allowMainFileUpload = false,
    allowOtherFilesUpload = false,
  } = props;

  const files = useFormFileUpload({
    defaultValue: null,
    accept: "file",
    maxSize: 15 * 1024 * 1024,
    validate: (value) => validateFile(value, allowMainFileUpload),
    allowMultipleFiles: true,
  });

  const mainFile = useFormFileUpload({
    defaultValue: null,
    accept: "image",
    maxSize: 15 * 1024 * 1024,
    validate: (value) =>
      validateMainFile(value, files.value, allowMainFileUpload),
    dependsOn: [files.value],
  });

  const handleClose = () => {
    onClose();
    files.reset();
    mainFile.reset();
  };

  const formValidator = useFormValidator([files, mainFile]);

  const handleSubmit = async () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      onSubmit({
        mainFile: mainFile.value?.[0] ?? null,
        files: files.value ?? [],
      });
      files.reset();
      mainFile.reset();
    }
  };

  return (
    <Dialog
      closeAfterTransition={false}
      className="file_upload_popup__comment_wrapper"
      onClose={() => handleClose()}
      open={isOpen}
    >
      <Typography
        className="cui_light_text"
        variant="h5"
        gutterBottom
        data-cy="file-upload-popup-heading"
      >
        {t("files_preview.popup.title")}
      </Typography>
      {allowMainFileUpload && (
        <div className="file_upload_popup__comment_input_wrapper">
          <FormFileUpload
            {...mainFile.fieldProps}
            label="toc_create.files.main_file.title"
            dataCy="main-file-upload-popup-file"
          />
        </div>
      )}
      {allowOtherFilesUpload && (
        <div className="file_upload_popup__comment_input_wrapper">
          <FormFileUpload
            {...files.fieldProps}
            label="toc_create.files.sub_files.title"
            dataCy="file-upload-popup-file"
          />
        </div>
      )}
      <div>
        <Button
          className="cui_button file_upload_popup__submit_button"
          variant="contained"
          onClick={handleSubmit}
          disabled={formValidator.isSubmitDisabled}
          data-cy="file-upload-popup-submit"
        >
          {t("files_preview.popup.submit")}
        </Button>
      </div>
    </Dialog>
  );
}
