import React from "react";
import { useTranslation } from "react-i18next";
import { FormControl, FormHelperText, FormLabel } from "@mui/material";
import { CKEditor } from "@ckeditor/ckeditor5-react";
import {
  ClassicEditor,
  Essentials,
  Paragraph,
  Bold,
  Italic,
  Heading,
  Link,
  List,
} from "ckeditor5";
import "./FormRichTextField.css";
// eslint-disable-next-line import/no-unresolved
import "ckeditor5/ckeditor5.css";

interface IProps {
  label?: string;
  requiredLabel?: boolean;
  onChange: (newValue: string) => void;
  onBlur?: () => void;
  helperText?: string;
  value: string;
  error?: boolean;
  className?: string;
  placeholder?: string;
  dataCy?: string;
}

function FormRichTextField(props: IProps) {
  const {
    label,
    requiredLabel,
    onChange,
    onBlur,
    helperText,
    value,
    error,
    className,
    placeholder,
    dataCy = "",
  } = props;
  const { t } = useTranslation();

  return (
    <FormControl
      className={`${className} form_rich_text_field__wrapper ${
        error && "form_rich_text_field__error"
      }`}
    >
      {label && (
        <FormLabel className="cui_label" data-cy={`${dataCy}-label`}>
          {requiredLabel ? `${t(label)} *` : t(label)}
        </FormLabel>
      )}
      <div data-cy={`${dataCy}-field`}>
        <CKEditor
          data={value}
          onChange={(event, editor) => {
            onChange(editor.getData());
          }}
          onBlur={onBlur}
          editor={ClassicEditor}
          config={{
            licenseKey: "GPL",
            plugins: [Essentials, Paragraph, Italic, Bold, Heading, Link, List],
            toolbar: [
              "bold",
              "italic",
              "bulletedList",
              "numberedList",
              "|",
              "undo",
              "redo",
            ],
            placeholder: t(placeholder ?? ""),
          }}
        />
        <FormHelperText error={error}>{helperText}</FormHelperText>
      </div>
    </FormControl>
  );
}

export default FormRichTextField;
