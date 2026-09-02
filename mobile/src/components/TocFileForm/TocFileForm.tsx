import React from "react";
import "./TocFileForm.css";
import { useTranslation } from "react-i18next";
import { Button } from "@mui/material";
import { useFormFileUpload } from "../../hooks/useFormFileUpload";
import FormFileUpload from "../FormFileUpload/FormFileUpload";
import { ITocFileFormData } from "../../@type/ITocFileFormData";

interface IProps {
  onSubmit: (data: ITocFileFormData) => void;
}

export default function TocFileForm(props: IProps) {
  const { onSubmit } = props;
  const { t } = useTranslation();

  const mainFile = useFormFileUpload({
    defaultValue: null,
    accept: "image",
    maxSize: 15 * 1024 * 1024,
  });

  const files = useFormFileUpload({
    defaultValue: null,
    accept: "file",
    maxSize: 15 * 1024 * 1024,
    allowMultipleFiles: true,
  });

  const handleSubmit = () => {
    onSubmit({ mainFile: mainFile.value, files: files.value });
  };

  return (
    <div className="toc_file_form__wrapper">
      <FormFileUpload
        {...mainFile.fieldProps}
        label="toc_create.files.main_file.title"
        dataCy="toc-file-form-main-file"
      />
      <FormFileUpload
        {...files.fieldProps}
        label="toc_create.files.sub_files.title"
        dataCy="toc-file-form-regular-file"
      />
      <Button
        className="cui_button toc_file_form__submit"
        type="submit"
        variant="contained"
        onClick={handleSubmit}
        data-cy="toc-file-form-submit"
      >
        {t("toc_form.submit")}
      </Button>
    </div>
  );
}
