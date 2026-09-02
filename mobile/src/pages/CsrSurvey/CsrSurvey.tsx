import React, { useEffect, useState } from "react";
import "./CsrSurvey.css";
import { useTranslation } from "react-i18next";
import { Button, Paper, Typography } from "@mui/material";
import { useNavigate, useParams } from "react-router";
import { StatusCodes } from "http-status-codes";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { toastError } from "../../utils/api";
import { setBreadcrumbs } from "../../redux/slices/breadcrumbSlice";
import { CSR_STATUS, CSR_TYPE, IDB_DATABASE } from "../../constants/constants";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import { getCustomerServiceRecordById } from "../../api/getCustomerServiceRecordById";
import { setCsrDetail } from "../../redux/slices/csrDetailSlice";
import {
  IPutCustomerServiceRecordApiPayload,
  putCustomerServiceRecord,
} from "../../api/putCustomerServiceRecord";
import CsrSurveyForm from "../../components/CsrSurveyForm/CsrSurveyForm";
import { ICsrSurveyFormData } from "../../@type/ICsrSurveyFormData";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.csr.home", link: ROUTES.csr.home },
  { title: "breadcrumb.csr.details", link: "" },
  { title: "breadcrumb.csr.survey.home", link: "" },
];

function CsrSurvey() {
  useDrawer(ROUTES.csr.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const { csrId } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const data = useAppSelector((state) => state.csrDetail.data);
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const [surveyFormData, setSurveryFormData] = useState<ICsrSurveyFormData>();
  const [submitClickCounter, setSubmitClickCounter] = useState(0);

  const isCommissioningCsr = data?.type === CSR_TYPE.commissioning;

  const handleSurveyFormChange = (newSurveryFormData: ICsrSurveyFormData) => {
    setSurveryFormData(newSurveryFormData);
  };

  const handleSubmit = async () => {
    setSubmitClickCounter((prev) => prev + 1);
    if (surveyFormData?.isFormErrorFree) {
      dispatch(showMainLoader(true));
      const params: IPutCustomerServiceRecordApiPayload = {
        "@id": data?.["@id"] ?? "",
        data: { surveyFormData },
      };
      if (isOnline) {
        const response = await putCustomerServiceRecord(params);
        if (response.status === StatusCodes.OK && response.data) {
          dispatch(setToastMessage("csr_update.success"));
          navigate(`${ROUTES.csr.details}/${csrId}`);
        } else {
          toastError(dispatch, response);
        }
      } else {
        await registerSyncEventWithApiPayload({
          eventName: IDB_DATABASE.stores.sync_put_csr,
          payload: params,
        });
        dispatch(setToastMessage("csr_update.offline"));
        navigate(ROUTES.csr.home);
      }
      dispatch(showMainLoader(false));
    }
  };

  const fetchCsrDetail = async () => {
    dispatch(showMainLoader(true));
    const response = await getCustomerServiceRecordById({ id: csrId ?? "" });
    dispatch(showMainLoader(false));
    if (response.data) {
      dispatch(setCsrDetail(response.data));
    } else {
      toastError(dispatch, response);
    }
  };

  const updateBreadcrumbs = () => {
    const newBreadcrumbs: Array<IBreadcrumb> = [
      { title: "breadcrumb.csr.home", link: ROUTES.csr.home },
      {
        title: "breadcrumb.csr.details",
        link: `${ROUTES.csr.details}/${csrId}`,
        appendText: `(#${csrId ?? ""})`,
      },
      { title: "breadcrumb.csr.survey.home", link: "" },
    ];
    dispatch(setBreadcrumbs(newBreadcrumbs));
  };

  useEffect(() => {
    fetchCsrDetail();
    updateBreadcrumbs();
    return () => {
      dispatch(setCsrDetail(null));
    };
  }, []);

  if (
    !data ||
    !isCommissioningCsr ||
    ![CSR_STATUS.COMPLETED, CSR_STATUS.CLOSED].includes(data.status)
  )
    return <div />;

  return (
    <div className="csr_survey__wrapper">
      <Typography
        variant="h5"
        className="cui_light_text"
        data-cy="csr-survey-heading"
      >
        {t("csr_survey.heading")}
      </Typography>
      <Paper className="csr_survey__form">
        <CsrSurveyForm
          onChange={handleSurveyFormChange}
          submitClickCounter={submitClickCounter}
          answerSurveyCustomerServiceRecords={
            data.answerSurveyCustomerServiceRecords ?? []
          }
        />
        <Button
          className="cui_button"
          variant="contained"
          onClick={handleSubmit}
          disabled={isCommissioningCsr && surveyFormData?.isSubmitDisabled}
          data-cy="csr-survey-submit"
        >
          {t("csr_survey.submit")}
        </Button>
      </Paper>
    </div>
  );
}

export default CsrSurvey;
