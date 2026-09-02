import React from "react";
import { useTranslation } from "react-i18next";
import { FormLabel as MuiFormLabel, Typography } from "@mui/material";
import "./FormLabel.css";

interface IProps {
  label: string;
  subLabel?: string;
  requiredLabel?: boolean;
  dataCy?: string;
}

function FormLabel(props: IProps) {
  const { label, subLabel, requiredLabel, dataCy = "" } = props;
  const { t } = useTranslation();

  return (
    <div className="form_field__label_wrapper">
      <MuiFormLabel
        className="cui_label"
        htmlFor={label}
        data-cy={`${dataCy}-label`}
      >
        {requiredLabel ? `${t(label)} *` : t(label)}
      </MuiFormLabel>
      <Typography
        className="form_field__sub_label"
        data-cy={`${dataCy}-sub-label`}
        variant="caption"
      >
        {t(subLabel ?? "")}
      </Typography>
    </div>
  );
}

export default FormLabel;
