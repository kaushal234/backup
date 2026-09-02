import React, { useEffect, useMemo, useState } from "react";
import Box from "@mui/material/Box";
import TextField from "@mui/material/TextField";
import Autocomplete from "@mui/material/Autocomplete";
import { debounce } from "@mui/material/utils";
import { useTranslation } from "react-i18next";
import { FormControl, FormLabel } from "@mui/material";
import { IDropdownItem } from "../../@type/IDropdownItem";
import {
  getHighlightParts,
  handleDropdownClose,
  handleDropdownOpen,
} from "../../utils/utils";
import "./FormMultiSelectApiAutoCompleteDropdown.css";

interface IProps {
  label?: string;
  requiredLabel?: boolean;
  fetchData: (inputValue: string) => Promise<Array<IDropdownItem>>;
  value: Array<IDropdownItem>;
  onChange: (value: Array<IDropdownItem>) => void;
  error?: boolean;
  helperText?: string;
  onBlur?: () => void;
  placeholder?: string;
  optionsPlaceholder?: string;
  triggerSearchAtChar?: number;
  id?: string;
  searchDebounceMs?: number;
  dataCy?: string;
  disabled?: boolean;
}

function FormMultiSelectApiAutoCompleteDropdown(props: IProps) {
  const {
    label,
    requiredLabel,
    fetchData,
    onChange: setValue,
    value,
    error,
    helperText,
    onBlur,
    placeholder,
    optionsPlaceholder,
    id,
    triggerSearchAtChar = 3,
    searchDebounceMs = 400,
    dataCy,
    disabled,
  } = props;
  const { t } = useTranslation();
  const [inputValue, setInputValue] = useState("");
  const [options, setOptions] = useState<IDropdownItem[]>([]);
  const [loading, setLoading] = useState(false);

  const fetch = useMemo(
    () =>
      debounce(
        async (
          newInputValue: string,
          callback: (results: IDropdownItem[]) => void
        ) => {
          if (newInputValue.length < triggerSearchAtChar) {
            callback([]);
            return;
          }
          callback(await fetchData(newInputValue));
        },
        searchDebounceMs
      ),
    []
  );

  useEffect(() => {
    if (inputValue === "") {
      setOptions(value);
      setLoading(false);
      return undefined;
    }

    fetch(inputValue, (results: IDropdownItem[]) => {
      let newOptions: IDropdownItem[] = [];

      if (value) {
        newOptions = [...value];
      }

      if (results) {
        newOptions = [...newOptions, ...results];
      }

      setOptions(newOptions);
      setLoading(false);
    });

    return undefined;
  }, [value, inputValue, fetch]);

  let noOptionsText = "";
  if (inputValue.length < triggerSearchAtChar) {
    if (optionsPlaceholder) {
      noOptionsText = t(optionsPlaceholder);
    } else {
      noOptionsText = t("common.search_min_char", {
        minChars: triggerSearchAtChar,
      });
    }
  } else {
    noOptionsText = t("common.no_match");
  }

  return (
    <FormControl className="cui_w-100">
      {label && (
        <FormLabel
          className="cui_label"
          htmlFor={id}
          data-cy={`${dataCy}-label`}
        >
          {requiredLabel ? `${t(label)} *` : t(label)}
        </FormLabel>
      )}
      <Autocomplete
        multiple
        loading={loading}
        getOptionLabel={(option) =>
          typeof option === "string" ? option : option.text
        }
        filterOptions={(x) => x}
        options={options}
        autoComplete
        includeInputInList
        filterSelectedOptions
        value={value}
        disabled={disabled}
        noOptionsText={noOptionsText}
        onChange={(event, newValue) => {
          const map = new Map<string, number>();
          newValue.forEach((item) =>
            map.set(item.id, (map.get(item.id) ?? 0) + 1)
          );
          const filteredValues = newValue.filter(
            (item) => map.get(item.id) === 1
          );
          setOptions(filteredValues);
          setValue(filteredValues);
        }}
        onInputChange={(event, newInputValue) => {
          if (!(newInputValue.length < triggerSearchAtChar)) {
            setOptions([]);
            setLoading(true);
          }
          setInputValue(newInputValue);
        }}
        data-cy={`${dataCy}-select`}
        renderInput={(params) => (
          <TextField
            {...params}
            className={`cui_input_wrapper  ${
              value.length > 0 ? "cui_padding_left" : ""
            }`}
            id={id}
            fullWidth
            error={error}
            helperText={helperText}
            onBlur={onBlur}
            placeholder={t(placeholder ?? "")}
          />
        )}
        renderOption={(renderProps, option, state) => {
          const parts = getHighlightParts(option.text, state.inputValue);
          return (
            <li
              {...renderProps}
              key={option.id}
              data-cy={`${dataCy}-select-item-${state.index}`}
              aria-selected={
                value.find((item) => item.id === option.id) ? "true" : "false"
              }
              role="option"
            >
              <div>
                {parts.map((part, index) => (
                  <Box
                    // eslint-disable-next-line react/no-array-index-key
                    key={index}
                    component="span"
                    sx={{ fontWeight: part.highlight ? "bold" : "regular" }}
                  >
                    {part.text}
                  </Box>
                ))}
              </div>
            </li>
          );
        }}
        onOpen={handleDropdownOpen}
        onClose={handleDropdownClose}
      />
    </FormControl>
  );
}

export default FormMultiSelectApiAutoCompleteDropdown;
