import React from "react";
import "./ErSearch.css";
import SearchIcon from "@mui/icons-material/Search";
import { IconButton, Paper } from "@mui/material";
import { useFormField } from "../../hooks/useFormField";
import FormField from "../FormField/FormField";
import { useFormValidator } from "../../hooks/useFormValidator";

interface IProps {
  onSubmit: (value: string) => void;
}

function ErSearch(props: IProps) {
  const { onSubmit } = props;

  const serialNumber = useFormField({
    defaultValue: "",
    requiredError: "er_finder.search.error",
  });

  const formValidator = useFormValidator([serialNumber]);

  const handleTocSearch = async () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      onSubmit(serialNumber.value);
    }
  };

  return (
    <Paper className="er_search__search_wrapper" elevation={1}>
      <div className="er_search__search_input_wrapper">
        <FormField
          {...serialNumber.fieldProps}
          placeholder="er_finder.search.placeholder"
          dataCy="er-search"
        />
      </div>
      <IconButton
        className="er_search__search_icon_wrapper"
        onClick={handleTocSearch}
        disabled={formValidator.isSubmitDisabled}
        data-cy="er-search-submit"
      >
        <SearchIcon />
      </IconButton>
    </Paper>
  );
}

export default ErSearch;
