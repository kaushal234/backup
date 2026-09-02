import React, { ReactElement, useMemo } from "react";
import { Theme, useTheme } from "@mui/material/styles";
import OutlinedInput from "@mui/material/OutlinedInput";
import MenuItem from "@mui/material/MenuItem";
import Select, { SelectChangeEvent } from "@mui/material/Select";
import {
  FormControl,
  FormHelperText,
  FormLabel,
  IconButton,
  InputAdornment,
  MenuProps,
} from "@mui/material";
import ClearIcon from "@mui/icons-material/Clear";
import "./FormSingleSelectDropdown.css";
import { useTranslation } from "react-i18next";
import HelpOutlineIcon from "@mui/icons-material/HelpOutline";
import { IDropdownItem } from "../../@type/IDropdownItem";
import MobileTooltip from "../MobileTooltip/MobileTooltip";
import { handleDropdownClose, handleDropdownOpen } from "../../utils/utils";

const ITEM_HEIGHT = 48;
const ITEM_PADDING_TOP = 8;
const CustomMenuProps: Partial<MenuProps> = {
  PaperProps: {
    style: {
      maxHeight: ITEM_HEIGHT * 4.5 + ITEM_PADDING_TOP,
      width: 250,
      overflowX: "auto",
    },
  },
  disableScrollLock: true,
};

function getStyles(name: string, personName: string, theme: Theme) {
  return {
    fontWeight:
      personName === name
        ? theme.typography.fontWeightMedium
        : theme.typography.fontWeightRegular,
  };
}

interface IProps {
  label?: string;
  requiredLabel?: boolean;
  list: Array<IDropdownItem>;
  value: IDropdownItem | null;
  onChange: (value: IDropdownItem | null) => void;
  error?: boolean;
  warning?: boolean;
  helperText?: string;
  onBlur?: () => void;
  placeholder?: string;
  dataCy?: string;
  disabled?: boolean;
  labelElement?: ReactElement;
}

function FormSingleSelectDropdown(props: IProps) {
  const {
    label,
    requiredLabel,
    value,
    list: rawList,
    onChange,
    placeholder,
    error,
    warning,
    helperText,
    onBlur,
    dataCy,
    disabled,
    labelElement,
  } = props;
  const { t } = useTranslation();
  const theme = useTheme();

  const list = useMemo(() => {
    return [...rawList].sort((a, b) => a.text.localeCompare(b.text));
  }, [rawList]);

  const handleChange = (event: SelectChangeEvent<string>) => {
    const newValue = event.target.value;
    if (!newValue) {
      onChange(null);
    } else {
      const match = list.find((item) => item.text === event.target.value);
      if (match) {
        onChange(match);
      }
    }
  };

  const handleClear = () => {
    onChange(null);
    onBlur?.();
  };

  const finalValue = useMemo(() => {
    let result = "";
    list.forEach((item) => {
      if (item.id === value?.id) {
        result = item.text;
      }
    });
    return result;
  }, [list, value]);

  return (
    <FormControl className="cui_w-100">
      {label && (
        <div className="form_single_select_dropdown__label_wrapper">
          <FormLabel
            className="cui_label"
            htmlFor="sales-organisation"
            data-cy={`${dataCy}-label`}
          >
            {requiredLabel ? `${t(label)} *` : t(label)}
          </FormLabel>
          {labelElement}
        </div>
      )}
      <Select
        color={warning ? "warning" : undefined}
        className={`cui_dropdown single_select_dropdown ${
          warning && "form_single_select_dropdown__warning"
        }`}
        displayEmpty
        value={t(finalValue)}
        onChange={handleChange}
        input={<OutlinedInput />}
        error={error}
        onBlur={onBlur}
        inputProps={{ "aria-label": "Without label" }}
        renderValue={(selected) => {
          if (selected.length === 0 && placeholder) {
            return <em>{t(placeholder)}</em>;
          }
          return t(value?.text ?? "");
        }}
        MenuProps={CustomMenuProps}
        endAdornment={
          finalValue && (
            <InputAdornment
              className="form_single_select_dropdown__close_icon_wrapper"
              position="end"
            >
              <IconButton onClick={handleClear}>
                <ClearIcon fontSize="small" />
              </IconButton>
            </InputAdornment>
          )
        }
        data-cy={`${dataCy}-select`}
        disabled={disabled}
        onOpen={handleDropdownOpen}
        onClose={handleDropdownClose}
      >
        {list.map((item, idx) => (
          <MenuItem
            key={item.text}
            value={item.text}
            style={getStyles(item.text, value?.text ?? "", theme)}
            data-cy={`${dataCy}-select-item-${idx}`}
          >
            <div className="single_select_dropdown__item">
              <div>{t(item.text)}</div>
              {item.infoText && (
                <MobileTooltip title={item.infoText}>
                  <HelpOutlineIcon />
                </MobileTooltip>
              )}
            </div>
          </MenuItem>
        ))}
      </Select>
      <FormHelperText
        className={`${warning && "cui_warning"}`}
        error={error}
        data-cy={`${dataCy}-error`}
        sx={{ ...(warning && { color: "warning.main" }) }}
      >
        {helperText}
      </FormHelperText>
    </FormControl>
  );
}

export default FormSingleSelectDropdown;
