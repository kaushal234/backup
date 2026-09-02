import React from "react";
import { useTranslation } from "react-i18next";
import {
  FormControl,
  FormControlLabel,
  FormHelperText,
  FormLabel,
  Radio,
  RadioGroup,
} from "@mui/material";
import { IRadioButton } from "../../@type/IRadioButton";
import "./FormRadioButtons.css";

interface IProps {
  label?: string;
  className?: string;
  value: string;
  onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
  error?: boolean;
  helperText?: string;
  onBlur?: () => void;
  isVertical?: boolean;
  list: Array<IRadioButton>;
  dataCy?: string;
  requiredLabel?: boolean;
  labelPlacement?: "end" | "start" | "top" | "bottom";
  isCentered?: boolean;
  disabled?: boolean;
}

function FormRadioButtons(props: IProps) {
  const {
    label,
    className,
    onChange,
    value,
    onBlur,
    error,
    helperText,
    isVertical = false,
    list,
    dataCy,
    requiredLabel,
    labelPlacement,
    isCentered,
    disabled,
  } = props;
  const { t } = useTranslation();

  return (
    <FormControl>
      {label && (
        <FormLabel className="cui_label" data-cy={`${dataCy}-title`}>
          {requiredLabel ? `${t(label)} *` : t(label)}
        </FormLabel>
      )}
      <RadioGroup
        row={!isVertical}
        className={`${className} ${
          isCentered && "form_radio_buttons__group_center"
        }`}
        value={value}
        onChange={onChange}
        onBlur={onBlur}
        data-cy={`${dataCy}-group`}
      >
        {list.map((radio, idx) => (
          <FormControlLabel
            key={radio.id}
            value={radio.id}
            control={<Radio />}
            label={t(radio.text)}
            data-cy={`${dataCy}-option-${idx}`}
            labelPlacement={labelPlacement}
            disabled={disabled}
          />
        ))}
      </RadioGroup>
      <FormHelperText error={error} data-cy={`${dataCy}-error`}>
        {helperText}
      </FormHelperText>
    </FormControl>
  );
}

export default FormRadioButtons;
