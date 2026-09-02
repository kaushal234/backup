import React from "react";
import "./CsrSearch.css";
import SearchIcon from "@mui/icons-material/Search";
import { IconButton, Paper } from "@mui/material";
import { useNavigate } from "react-router";
import { StatusCodes } from "http-status-codes";
import { useAppDispatch } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { useFormField } from "../../hooks/useFormField";
import { toastError } from "../../utils/api";
import { ROUTES } from "../../constants/routes";
import FormField from "../FormField/FormField";
import { getCustomerServiceRecordById } from "../../api/getCustomerServiceRecordById";
import { setCsrDetail } from "../../redux/slices/csrDetailSlice";
import { useFormValidator } from "../../hooks/useFormValidator";

function CsrSearch() {
  const navigate = useNavigate();
  const dispatch = useAppDispatch();

  const csrId = useFormField({
    defaultValue: "",
    requiredError: "csr_filter.search.error.required",
    isNumber: true,
  });

  const formValidator = useFormValidator([csrId]);

  const handleCsrSearch = async () => {
    formValidator.touchAll();

    if (formValidator.isFormErrorFree) {
      dispatch(showMainLoader(true));
      const response = await getCustomerServiceRecordById({
        id: csrId.value,
      });
      dispatch(showMainLoader(false));
      if (response.status === StatusCodes.OK && response.data) {
        dispatch(setCsrDetail(response.data));
        navigate(`${ROUTES.csr.details}/${response.data.id}`);
      } else {
        toastError(dispatch, response);
      }
    }
  };

  return (
    <Paper className="csr_search__search_toc_wrapper" elevation={1}>
      <div className="csr_search__search_input_wrapper">
        <FormField
          {...csrId.fieldProps}
          placeholder="csr_filter.search.placeholder"
          dataCy="csr-search"
        />
      </div>
      <IconButton
        className="csr_search__search_icon_wrapper"
        onClick={handleCsrSearch}
        data-cy="csr-search-submit"
        disabled={formValidator.isSubmitDisabled}
      >
        <SearchIcon />
      </IconButton>
    </Paper>
  );
}

export default CsrSearch;
