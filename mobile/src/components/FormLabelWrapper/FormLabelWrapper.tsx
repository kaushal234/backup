import React from "react";
import { useTranslation } from "react-i18next";
import { FormControlLabel } from "@mui/material";
import HelpOutlineIcon from "@mui/icons-material/HelpOutline";
import MobileTooltip from "../MobileTooltip/MobileTooltip";
import "./FormLabelWrapper.css";

interface ILabelWrapperProps {
  children: React.JSX.Element;
  label?: string;
  dataCy?: string;
  labelPlacement?: "end" | "start" | "top" | "bottom" | undefined;
  requiredLabel?: boolean;
  labelTooltip?: string;
}

function FormLabelWrapper(props: ILabelWrapperProps) {
  const {
    children,
    label,
    dataCy = "",
    labelPlacement,
    requiredLabel,
    labelTooltip,
  } = props;
  const { t } = useTranslation();

  if (label) {
    return (
      <FormControlLabel
        control={children}
        label={
          <div className="form_label_wrapper__label_wrapper">
            <div>{requiredLabel ? `${t(label)} *` : t(label)}</div>
            {labelTooltip && (
              <MobileTooltip title={t(labelTooltip)}>
                <HelpOutlineIcon />
              </MobileTooltip>
            )}
          </div>
        }
        data-cy={`${dataCy}-label`}
        labelPlacement={labelPlacement}
      />
    );
  }
  return children;
}

export default FormLabelWrapper;
