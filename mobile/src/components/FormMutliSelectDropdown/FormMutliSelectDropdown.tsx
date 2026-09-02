import React, { useMemo } from "react";
import { Theme, useTheme } from "@mui/material/styles";
import Box from "@mui/material/Box";
import OutlinedInput from "@mui/material/OutlinedInput";
import MenuItem from "@mui/material/MenuItem";
import Select, { SelectChangeEvent } from "@mui/material/Select";
import Chip from "@mui/material/Chip";
import {
  FormControl,
  FormHelperText,
  FormLabel,
  MenuProps,
} from "@mui/material";
import { useTranslation } from "react-i18next";
import HelpOutlineIcon from "@mui/icons-material/HelpOutline";
import { IDropdownItem } from "../../@type/IDropdownItem";
import MobileTooltip from "../MobileTooltip/MobileTooltip";
import "./FormMutliSelectDropdown.css";
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

function getStyles(name: string, personName: readonly string[], theme: Theme) {
  return {
    fontWeight: personName.includes(name)
      ? theme.typography.fontWeightMedium
      : theme.typography.fontWeightRegular,
  };
}

interface IProps {
  label?: string;
  list: Array<IDropdownItem>;
  value: Array<IDropdownItem>;
  onChange: (value: Array<IDropdownItem>) => void;
  error?: boolean;
  helperText?: string;
  onBlur?: () => void;
  placeholder?: string;
  dataCy?: string;
  disabled?: boolean;
}

function FormMutliSelectDropdown(props: IProps) {
  const {
    label,
    value: rawValue,
    list: rawList,
    onChange,
    placeholder,
    error,
    helperText,
    onBlur,
    dataCy,
    disabled,
  } = props;
  const theme = useTheme();
  const { t } = useTranslation();

  const list = useMemo(() => {
    return [...rawList].sort((a, b) => a.text.localeCompare(b.text));
  }, [rawList]);

  const value = useMemo(() => {
    return [...rawValue].sort((a, b) => a.text.localeCompare(b.text));
  }, [rawValue]);

  const handleChange = (event: SelectChangeEvent<Array<string>>) => {
    const newValue = event.target.value;
    if (typeof newValue !== "string") {
      const selectedValues: Array<IDropdownItem> = [];
      newValue.forEach((item) => {
        const match = list.find((listItem) => listItem.text === item);
        if (match) {
          selectedValues.push(match);
        }
        onChange(selectedValues);
      });
    }
  };

  const handleRemove = (removedItem: string) => {
    onChange(value.filter((item) => item.text !== removedItem));
    onBlur?.();
  };

  const valueTextList: Array<string> = React.useMemo(() => {
    const result: Array<string> = [];
    value.forEach((item) => {
      const match = list.find((listItem) => listItem.id === item.id);
      if (match) {
        result.push(match.text);
      }
    });
    return result;
  }, [value, list]);

  return (
    <FormControl className="cui_w-100">
      {label && (
        <FormLabel className="cui_label" data-cy={`${dataCy}-label`}>
          {t(label)}
        </FormLabel>
      )}
      <Select
        className="cui_dropdown"
        multiple
        displayEmpty
        value={valueTextList}
        onChange={handleChange}
        input={<OutlinedInput />}
        error={error}
        onBlur={onBlur}
        inputProps={{ "aria-label": "Without label" }}
        data-cy={`${dataCy}-select`}
        renderValue={(selected) => {
          if (selected.length === 0 && placeholder) {
            return <em>{t(placeholder)}</em>;
          }
          return (
            <Box sx={{ display: "flex", flexWrap: "wrap", gap: 0.5 }}>
              {selected.map((item) => (
                <Chip
                  key={item}
                  label={t(item)}
                  onDelete={(e) => {
                    e.stopPropagation();
                    handleRemove(item);
                  }}
                  onMouseDown={(e) => {
                    e.stopPropagation();
                    e.preventDefault();
                  }}
                />
              ))}
            </Box>
          );
        }}
        MenuProps={CustomMenuProps}
        disabled={disabled}
        onOpen={handleDropdownOpen}
        onClose={handleDropdownClose}
      >
        {list.map((item, idx) => (
          <MenuItem
            key={item.text}
            value={item.text}
            style={getStyles(item.text, valueTextList, theme)}
            data-cy={`${dataCy}-select-item-${idx}`}
          >
            <div className="multi_select_dropdown__item">
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
      <FormHelperText error={error}>{helperText}</FormHelperText>
    </FormControl>
  );
}

export default FormMutliSelectDropdown;
