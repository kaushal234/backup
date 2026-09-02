import React, { useState } from "react";
import "./TocAiSearch.css";
import { useTranslation } from "react-i18next";
import { Button, FormHelperText, Link, Paper, Typography } from "@mui/material";
import { useNavigate } from "react-router";
import { StatusCodes } from "http-status-codes";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { useAppDispatch } from "../../hooks/hooks";
import FormField from "../../components/FormField/FormField";
import { useFormField } from "../../hooks/useFormField";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { getAllTechnicianOnCallBySearchText } from "../../api/getAllTechnicianOnCallBySearchText";
import { toastError } from "../../utils/api";
import { useFormValidator } from "../../hooks/useFormValidator";
import { ISearchOutput } from "../../@type/IGetAllTechnicianOnCallBySearchTextResponse";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.toc.home", link: ROUTES.toc.home },
  { title: "breadcrumb.toc.search", link: "" },
];

function TocAiSearch() {
  useDrawer(ROUTES.toc.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const [data, setData] = useState<Array<ISearchOutput> | null>(null);

  const question = useFormField({
    defaultValue: "",
    requiredError: "toc_ai_search.question.error.required",
  });

  const formValidator = useFormValidator([question]);

  const handleSubmit = async () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      dispatch(showMainLoader(true));
      setData(null);
      const response = await getAllTechnicianOnCallBySearchText({
        question: question.value,
      });
      if (response.status === StatusCodes.OK && response.data) {
        setData(response.data.results["hydra:member"]);
      } else {
        toastError(dispatch, response);
      }
      dispatch(showMainLoader(false));
    }
  };

  return (
    <div className="toc_ai_search__wrapper">
      <Typography
        variant="h5"
        className="cui_light_text"
        data-cy="toc-update-heading"
      >
        {t("toc_ai_search.heading")}
      </Typography>
      <Paper className="toc_ai_search__form">
        <FormHelperText>{t("toc_ai_search.sub_heading")}</FormHelperText>
        <FormField
          {...question.fieldProps}
          label="toc_ai_search.question.title"
          id="question"
          placeholder="toc_ai_search.question.placeholder"
          rows={4}
          dataCy="toc-ai-search-question"
        />
        <Button
          disabled={formValidator.isSubmitDisabled}
          className="cui_button toc_parts_form__submit"
          type="submit"
          variant="contained"
          onClick={handleSubmit}
          data-cy="toc-form-submit"
        >
          {t("toc_parts_form.submit")}
        </Button>
      </Paper>
      {data && (
        <Paper className="toc_ai_search__form">
          <Typography className="cui_light_text" variant="h6">
            {t("toc_ai_search.result")}
          </Typography>
          <div className="toc_ai_search__list">
            {data.map((item) => (
              <Link
                component="button"
                variant="body2"
                onClick={() => {
                  navigate(`${ROUTES.toc.details}/${item.id}`);
                }}
                className="toc_ai_search__list_item"
              >
                <span className="toc_ai_search__item_bold">{item.id}</span>
                {` : ${item.description}`}
              </Link>
            ))}
            {data.length === 0 && (
              <Typography variant="body1" className="cui_light_text">
                {t("common.no_match")}
              </Typography>
            )}
          </div>
        </Paper>
      )}
    </div>
  );
}

export default TocAiSearch;
