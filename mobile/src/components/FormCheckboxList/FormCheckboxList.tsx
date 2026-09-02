import React from "react";
import { useTranslation } from "react-i18next";
import {
  Checkbox,
  FormControl,
  FormGroup,
  FormHelperText,
  FormLabel,
} from "@mui/material";
import { ICheckboxListItem } from "../../@type/ICheckboxListItem";
import FormLabelWrapper from "../FormLabelWrapper/FormLabelWrapper";
import "./FormCheckboxList.css";

interface IProps {
  label?: string;
  className?: string;
  value: Array<string>;
  onChange: (event: React.ChangeEvent<HTMLInputElement>, index: number) => void;
  error?: boolean;
  helperText?: string;
  onBlur?: () => void;
  list: Array<ICheckboxListItem>;
  dataCy?: string;
  requiredLabel?: boolean;
}

function FormCheckboxList(props: IProps) {
  const {
    label,
    className,
    onChange,
    value,
    onBlur,
    error,
    helperText,
    list,
    dataCy,
    requiredLabel,
  } = props;
  const { t } = useTranslation();

  return (
    <FormControl>
      {label && (
        <FormLabel className="cui_label" data-cy={`${dataCy}-title`}>
          {requiredLabel ? `${t(label)} *` : t(label)}
        </FormLabel>
      )}
      <FormGroup className={className} data-cy={`${dataCy}-group`}>
        {list.map((item, idx) => (
          <FormControl
            className="form_checkbox_list__wrapper"
            data-cy={`${dataCy}-${idx}-field`}
            key={item.id}
          >
            <FormLabelWrapper
              label={item.text}
              labelPlacement="start"
              dataCy={`${dataCy}-${idx}-option`}
            >
              <Checkbox
                onChange={(e) => onChange(e, idx)}
                onBlur={onBlur}
                checked={value.includes(item.id)}
                className={className}
                disabled={item.isDisabled}
              />
            </FormLabelWrapper>
          </FormControl>
        ))}
      </FormGroup>
      <FormHelperText error={error} data-cy={`${dataCy}-error`}>
        {helperText}
      </FormHelperText>
    </FormControl>
  );
}

export default FormCheckboxList;
