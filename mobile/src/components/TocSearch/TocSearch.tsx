import React from "react";
import "./TocSearch.css";
import SearchIcon from "@mui/icons-material/Search";
import { IconButton, Paper } from "@mui/material";
import { useNavigate } from "react-router";
import { StatusCodes } from "http-status-codes";
import { useAppDispatch } from "../../hooks/hooks";

import { showMainLoader } from "../../redux/slices/loaderSlice";
import { useFormField } from "../../hooks/useFormField";
import { getTechnicianOnCallById } from "../../api/getTechnicianOnCallById";
import { toastError } from "../../utils/api";
import { ROUTES } from "../../constants/routes";
import { setTocDetail } from "../../redux/slices/tocDetailSlice";
import FormField from "../FormField/FormField";
import { useFormValidator } from "../../hooks/useFormValidator";

function TocSearch() {
  const navigate = useNavigate();
  const dispatch = useAppDispatch();

  const tocId = useFormField({
    defaultValue: "",
    requiredError: "toc.filter.search.error.required",
    isNumber: true,
  });

  const formValidator = useFormValidator([tocId]);

  const handleTocSearch = async () => {
    formValidator.touchAll();

    if (formValidator.isFormErrorFree) {
      dispatch(showMainLoader(true));
      const response = await getTechnicianOnCallById({
        id: tocId.value,
      });
      dispatch(showMainLoader(false));
      if (response.status === StatusCodes.OK && response.data) {
        dispatch(setTocDetail(response.data));
        navigate(`${ROUTES.toc.details}/${response.data.id}`);
      } else {
        toastError(dispatch, response);
      }
    }
  };

  return (
    <Paper className="toc_search__search_toc_wrapper" elevation={1}>
      <div className="toc_search__search_input_wrapper">
        <FormField
          {...tocId.fieldProps}
          placeholder="toc.filter.search.placeholder"
          dataCy="toc-search"
        />
      </div>
      <IconButton
        className="toc_search__search_icon_wrapper"
        onClick={handleTocSearch}
        data-cy="toc-search-submit"
        disabled={formValidator.isSubmitDisabled}
      >
        <SearchIcon />
      </IconButton>
    </Paper>
  );
}

export default TocSearch;
