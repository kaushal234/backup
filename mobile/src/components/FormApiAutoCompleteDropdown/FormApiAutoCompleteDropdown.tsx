import React, { useEffect, useMemo, useRef, useState } from "react";
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
import "./FormApiAutoCompleteDropdown.css";

interface IProps {
  label?: string;
  requiredLabel?: boolean;
  fetchData: (inputValue: string) => Promise<Array<IDropdownItem>>;
  value: IDropdownItem | null;
  onChange: (value: IDropdownItem | null) => void;
  error?: boolean;
  warning?: boolean;
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

function FormApiAutoCompleteDropdown(props: IProps) {
  const {
    label,
    requiredLabel,
    fetchData,
    onChange: setValue,
    value,
    error,
    warning,
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
  const inputRef = useRef<HTMLInputElement>(null);

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
      setOptions(value ? [value] : []);
      setLoading(false);
      return undefined;
    }

    fetch(inputValue, (results: IDropdownItem[]) => {
      let newOptions: IDropdownItem[] = [];

      if (value) {
        newOptions = [value];
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
        color={warning ? "warning" : undefined}
        className={`cui_dropdown single_select_dropdown ${
          warning && "form_single_select_dropdown__warning"
        }`}
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
          setOptions(newValue ? [newValue, ...options] : options);
          setValue(newValue);
          inputRef.current?.querySelector("input")?.blur();
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
            className="cui_input_wrapper"
            id={id}
            fullWidth
            error={error}
            helperText={helperText}
            onBlur={onBlur}
            placeholder={t(placeholder ?? "")}
            ref={inputRef}
            slotProps={{
              formHelperText: {
                className: `${warning && "cui_warning"}`,
                error,
                sx: { ...(warning && { color: "warning.main" }) },
              },
            }}
          />
        )}
        renderOption={(renderProps, option, state) => {
          const parts = getHighlightParts(option.text, state.inputValue);
          return (
            <li
              {...renderProps}
              key={option.id}
              data-cy={`${dataCy}-select-item-${state.index}`}
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

export default FormApiAutoCompleteDropdown;
