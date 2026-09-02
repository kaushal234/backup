import React, { useState } from "react";
import "./InterventionPopUp.css";
import { Button, Dialog, Typography } from "@mui/material";
import { t } from "i18next";
import dayjs from "dayjs";
import { StatusCodes } from "http-status-codes";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { useFormSingleSelectDropdown } from "../../hooks/useFormSingleSelectDropdown";
import FormSingleSelectDropdown from "../FormSingleSelectDropdown/FormSingleSelectDropdown";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import {
  CSR_TYPE,
  DATE_FORMAT,
  IDB_DATABASE,
  INTERVENTION_STATUS,
  TOC_STATUS,
} from "../../constants/constants";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { toastError } from "../../utils/api";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import { useFormField } from "../../hooks/useFormField";
import FormField from "../FormField/FormField";
import { useFormValidator } from "../../hooks/useFormValidator";
import { useFormDatePicker } from "../../hooks/useFormDatePicker";
import FormDatePicker from "../FormDatePicker/FormDatePicker";
import { IGetCustomerServiceRecordResponse } from "../../@type/IGetCustomerServiceRecordResponse";
import { createInterventionStatusDropdownItem } from "../../utils/dropdown/interventionStatus";
import {
  IPutInterventionApiPayload,
  putIntervention,
} from "../../api/putIntervention";
import { getIdFromIri } from "../../utils/utils";
import { refreshCsrData } from "../../redux/slices/csrDetailSlice";
import {
  IPostCsrHourMeterApiPayload,
  postCsrHourMeter,
} from "../../api/postCsrHourMeter";
import { useFormSwitch } from "../../hooks/useFormSwitch";
import FormSwitch from "../FormSwitch/FormSwitch";
import CsrSurveyForm from "../CsrSurveyForm/CsrSurveyForm";
import { ICsrSurveyFormData } from "../../@type/ICsrSurveyFormData";
import {
  IPutCustomerServiceRecordApiPayload,
  putCustomerServiceRecord,
} from "../../api/putCustomerServiceRecord";
import {
  IPutTechnicianOnCallStatusApiPayload,
  putTechnicianOnCallStatus,
} from "../../api/putTechnicianOnCallStatus";
import { ITocDefectivePart } from "../../@type/ITocDefectivePart";
import TocStatusPartsTable from "../TocStatusPartsTable/TocStatusPartsTable";

const validateSymptoms = (value: string, isStatusSolved: boolean) => {
  if (value || !isStatusSolved) return "";
  return "toc_details.status_popup.symptoms.error.required";
};

const validateRootCause = (value: string, isStatusSolved: boolean) => {
  if (value || !isStatusSolved) return "";
  return "toc_details.status_popup.root_cause.error.required";
};

const validateSolution = (value: string, isStatusSolved: boolean) => {
  if (value || !isStatusSolved) return "";
  return "toc_details.status_popup.solution.error.required";
};

const validateStartDate = (data: {
  formattedStartDate: string | null;
  formattedEndDate: string | null;
  optional: boolean;
}) => {
  if (data.optional) {
    return "";
  }
  if (!data.formattedStartDate) {
    return "intervention_popup.start_date.error.required";
  }
  if (!data.formattedEndDate) {
    return "";
  }
  if (dayjs(data.formattedStartDate).isAfter(dayjs(data.formattedEndDate))) {
    return "intervention_popup.start_date.error.after_before";
  }
  return "";
};

const validateEndDate = (value: string, optional = false) => {
  if (value || optional) return "";
  return "intervention_popup.end_date.error.required";
};

const validateHourMeter = (newValue: string, oldValue: string) => {
  if (!oldValue || !newValue || +newValue >= +oldValue) return "";
  return "intervention_popup.hour_meter.error.min_value";
};

const validateThirdPartyJobDescription = (
  value: string,
  isTocMarkedSolved: boolean,
  thirdPartyName: string
) => {
  if (!isTocMarkedSolved || value || !thirdPartyName) return "";
  return "toc_details.status_popup.third_party_job_description.error.required";
};

const validateThirdPartyHours = (
  value: string,
  isTocMarkedSolved: boolean,
  thirdPartyName: string
) => {
  if (!isTocMarkedSolved || !thirdPartyName) return "";
  if (!value) {
    return "toc_details.status_popup.third_party_hours.error.required";
  }
  if (+value < 1) {
    return "toc_details.status_popup.third_party_hours.error.zero";
  }
  return "";
};

interface IProps {
  data: IGetCustomerServiceRecordResponse;
  isOpen: boolean;
  onClose: () => void;
  openingIntervention: boolean;
}

export default function InterventionPopUp(props: IProps) {
  const { data, isOpen, onClose, openingIntervention } = props;
  const dispatch = useAppDispatch();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const [surveyFormData, setSurveryFormData] = useState<ICsrSurveyFormData>();
  const [submitClickCounter, setSubmitClickCounter] = useState(0);
  const [defectiveParts, setDefectiveParts] = useState<
    Array<ITocDefectivePart>
  >([]);
  const [resetCounter, setResetCounter] = useState(0);

  const statusList = openingIntervention
    ? [
        INTERVENTION_STATUS.TO_CONTINUE,
        INTERVENTION_STATUS.STARTED,
        INTERVENTION_STATUS.SOLVED,
      ]
    : [INTERVENTION_STATUS.TO_CONTINUE, INTERVENTION_STATUS.SOLVED];

  const hourMeterOldValue = data.equipmentRecord.hourMeter?.toString() ?? "0";

  const isCommissioningCsr = data.type === CSR_TYPE.commissioning;

  const statusDropdownItemList = statusList.map((item) =>
    createInterventionStatusDropdownItem(item)
  );

  const status = useFormSingleSelectDropdown({
    defaultValue: null,
    list: statusDropdownItemList,
    requiredError: "intervention_popup.status.error.required",
  });

  const isStartingIntervention =
    status.value?.id === INTERVENTION_STATUS.STARTED;

  const isSolvingIntervention = status.value?.id === INTERVENTION_STATUS.SOLVED;

  const isEndOptional =
    openingIntervention &&
    (!status.value || status.value.id === INTERVENTION_STATUS.STARTED);

  const endDate = useFormDatePicker({
    defaultValue: null,
    validate: (value) =>
      validateEndDate(value?.format(DATE_FORMAT) ?? "", isEndOptional),
  });

  const startDate = useFormDatePicker({
    defaultValue: null,
    validate: (value) =>
      validateStartDate({
        formattedStartDate: value?.format(DATE_FORMAT) ?? "",
        formattedEndDate: endDate.formattedValue,
        optional: !openingIntervention,
      }),
  });

  const hourMeter = useFormField({
    defaultValue: "",
    isNumber: true,
    validate: (value) => validateHourMeter(value, hourMeterOldValue),
  });

  const showUpdateTocStatusPopup = useFormSwitch({
    defaultValue: false,
  });

  const symptoms = useFormField({
    defaultValue:
      (data.technicianOnCall?.originalTitle || data.technicianOnCall?.title) ??
      "",
    validate: (newValue) =>
      validateSymptoms(newValue, showUpdateTocStatusPopup.value),
  });

  const rootCause = useFormField({
    defaultValue: "",
    validate: (newValue) =>
      validateRootCause(newValue, showUpdateTocStatusPopup.value),
  });

  const solution = useFormField({
    defaultValue: "",
    validate: (newValue) =>
      validateSolution(newValue, showUpdateTocStatusPopup.value),
  });

  const thirdPartyJobDescription = useFormField({
    defaultValue: data.technicianOnCall?.thirdPartyJobDescription ?? "",
    validate: (newValue) =>
      validateThirdPartyJobDescription(
        newValue,
        showUpdateTocStatusPopup.value,
        data.technicianOnCall?.thirdPartyName ?? ""
      ),
    dependsOn: [status.value],
  });

  const thirdPartyHours = useFormField({
    defaultValue: data.technicianOnCall?.thirdPartyHours?.toString() ?? "",
    validate: (newValue) =>
      validateThirdPartyHours(
        newValue,
        showUpdateTocStatusPopup.value,
        data.technicianOnCall?.thirdPartyName ?? ""
      ),
    dependsOn: [status.value],
    isNumber: true,
  });

  const handleClose = () => {
    onClose();
    status.reset();
    startDate.reset();
    endDate.reset();
    hourMeter.reset();
    setResetCounter((prev) => prev + 1);
  };

  const formValidator = useFormValidator([
    status,
    startDate,
    endDate,
    hourMeter,
    symptoms,
    rootCause,
    solution,
    thirdPartyJobDescription,
    thirdPartyHours,
  ]);

  const handleSubmit = async () => {
    formValidator.touchAll();
    setSubmitClickCounter((prev) => prev + 1);
    if (
      formValidator.isFormErrorFree &&
      (!isCommissioningCsr || surveyFormData?.isFormErrorFree)
    ) {
      const interventionParams: IPutInterventionApiPayload = {
        id: getIdFromIri(data.openIntervention?.["@id"]),
        data: {
          status: status.value?.id ?? "",
        },
      };
      if (startDate.value) {
        interventionParams.data.startedAt = startDate.formattedValue ?? "";
      }
      if (endDate.value && !isStartingIntervention) {
        interventionParams.data.endedAt = endDate.formattedValue ?? "";
      }
      const hourMeterParams: IPostCsrHourMeterApiPayload = {
        hourMeter: +hourMeter.value,
        customerServiceRecordIri: data["@id"],
        equipmentRecordId: data.equipmentRecord.id,
      };
      const csrParams: IPutCustomerServiceRecordApiPayload = {
        "@id": data["@id"],
        data: { surveyFormData },
      };
      const tocParams: IPutTechnicianOnCallStatusApiPayload = {
        id: data.technicianOnCall?.id?.toString() ?? "",
        data: {
          status: TOC_STATUS.SOLVED,
          originalSymptoms: symptoms.value,
          originalRootCause: rootCause.value,
          originalSolution: solution.value,
          ...(thirdPartyJobDescription.value && {
            thirdPartyJobDescription: thirdPartyJobDescription.value,
          }),
          ...(thirdPartyHours.value && {
            thirdPartyHours: +thirdPartyHours.value,
          }),
          defectiveParts,
        },
      };
      if (isOnline) {
        dispatch(showMainLoader(true));
        const promises = [];
        promises.push(putIntervention(interventionParams));
        if (hourMeterParams.hourMeter) {
          promises.push(postCsrHourMeter(hourMeterParams));
        }
        if (isCommissioningCsr) {
          promises.push(putCustomerServiceRecord(csrParams));
        }
        const [interventionResponse] = await Promise.all(promises);
        if (interventionResponse.status === StatusCodes.OK) {
          dispatch(setToastMessage("intervention_popup.success"));
          if (showUpdateTocStatusPopup.value) {
            const response = await putTechnicianOnCallStatus(tocParams);
            if (response.status === StatusCodes.OK) {
              dispatch(setToastMessage("toc_update.success"));
            }
          }
          dispatch(refreshCsrData());
        } else {
          toastError(dispatch, interventionResponse);
          dispatch(showMainLoader(false));
        }
      } else {
        await registerSyncEventWithApiPayload({
          eventName: IDB_DATABASE.stores.sync_put_intervention,
          payload: interventionParams,
        });
        if (hourMeterParams.hourMeter) {
          await registerSyncEventWithApiPayload({
            eventName: IDB_DATABASE.stores.sync_post_csr_hour_meter,
            payload: hourMeterParams,
          });
        }
        if (isCommissioningCsr) {
          await registerSyncEventWithApiPayload({
            eventName: IDB_DATABASE.stores.sync_put_csr,
            payload: csrParams,
          });
        }
        if (showUpdateTocStatusPopup.value) {
          await registerSyncEventWithApiPayload({
            eventName: IDB_DATABASE.stores.sync_put_toc_status,
            payload: tocParams,
          });
        }
        dispatch(setToastMessage("intervention_popup.offline"));
      }
      handleClose();
    }
  };

  const handleSurveyFormChange = (newSurveryFormData: ICsrSurveyFormData) => {
    setSurveryFormData(newSurveryFormData);
  };

  return (
    <Dialog
      closeAfterTransition={false}
      className="intervention_popup__wrapper"
      onClose={() => handleClose()}
      open={isOpen}
    >
      <Typography
        className="cui_light_text"
        variant="h5"
        gutterBottom
        data-cy="intervention-popup-heading"
      >
        {t("intervention_popup.heading")}
      </Typography>
      <div className="intervention_popup__input_wrapper">
        <FormSingleSelectDropdown
          {...status.fieldProps}
          label="intervention_popup.status.title"
          requiredLabel
          placeholder="intervention_popup.status.placeholder"
          dataCy="intervention-status"
        />
      </div>
      {status.value && (
        <>
          {openingIntervention && (
            <div className="intervention_popup__input_wrapper">
              <FormDatePicker
                {...startDate.fieldProps}
                requiredLabel={openingIntervention}
                label="intervention_popup.start_date.title"
                dataCy="intervention-start-date"
              />
            </div>
          )}
          {!isStartingIntervention && (
            <div className="intervention_popup__input_wrapper">
              <FormDatePicker
                {...endDate.fieldProps}
                requiredLabel={!isEndOptional}
                label="intervention_popup.end_date.title"
                dataCy="intervention-end-date"
              />
            </div>
          )}
          <div className="intervention_popup__input_wrapper">
            <FormField
              {...hourMeter.fieldProps}
              label="intervention_popup.hour_meter.title"
              subLabel={`(${t(
                "intervention_popup.hour_meter.sub_title"
              )} : ${hourMeterOldValue})`}
              id="hour-meter"
              placeholder="intervention_popup.hour_meter.placeholder"
              dataCy="intervention-hour-meter"
            />
          </div>
          {isSolvingIntervention &&
            data.technicianOnCall?.id &&
            data.technicianOnCall?.status === TOC_STATUS.IN_PROGRESS && (
              <div className="intervention_popup__input_wrapper">
                <FormSwitch
                  {...showUpdateTocStatusPopup.fieldProps}
                  label="intervention_popup.update_toc_status"
                  dataCy="intervention-update-toc-status"
                />
              </div>
            )}
          {showUpdateTocStatusPopup.value && data.technicianOnCall && (
            <>
              <div className="toc_status_popup__input_wrapper">
                <FormField
                  {...symptoms.fieldProps}
                  label="toc_details.status_popup.symptoms.title"
                  subLabel="toc_details.status_popup.symptoms.sub_title"
                  id="symptoms"
                  placeholder="toc_details.status_popup.symptoms.placeholder"
                  dataCy="toc-status-popup-symptoms"
                  requiredLabel
                />
              </div>
              <div className="toc_status_popup__input_wrapper">
                <FormField
                  {...rootCause.fieldProps}
                  label="toc_details.status_popup.root_cause.title"
                  id="root-cause"
                  placeholder="toc_details.status_popup.root_cause.placeholder"
                  dataCy="toc-status-popup-root-cause"
                  requiredLabel
                />
              </div>
              <div className="toc_status_popup__input_wrapper">
                <FormField
                  {...solution.fieldProps}
                  label="toc_details.status_popup.solution.title"
                  id="solution"
                  placeholder="toc_details.status_popup.solution.placeholder"
                  dataCy="toc-status-popup-solution"
                  requiredLabel
                />
              </div>
              {data.technicianOnCall?.thirdPartyName && (
                <>
                  <div className="toc_status_popup__input_wrapper">
                    <FormField
                      {...thirdPartyJobDescription.fieldProps}
                      label="toc_details.status_popup.third_party_job_description.title"
                      id="thirdPartyJobDescription"
                      placeholder="toc_details.status_popup.third_party_job_description.placeholder"
                      dataCy="toc-status-popup-third-party-job-description"
                      requiredLabel
                    />
                  </div>
                  <div className="toc_status_popup__input_wrapper">
                    <FormField
                      {...thirdPartyHours.fieldProps}
                      label="toc_details.status_popup.third_party_hours.title"
                      id="thirdPartyHours"
                      placeholder="toc_details.status_popup.third_party_hours.placeholder"
                      dataCy="toc-status-popup-third-party-hours"
                      requiredLabel
                    />
                  </div>
                </>
              )}
              <div className="toc_status_popup__input_wrapper">
                <TocStatusPartsTable
                  data={data.technicianOnCall}
                  dataCy="toc-status"
                  setDefectiveParts={setDefectiveParts}
                />
              </div>
            </>
          )}
          {isCommissioningCsr && (
            <div>
              <CsrSurveyForm
                onChange={handleSurveyFormChange}
                submitClickCounter={submitClickCounter}
                resetCounter={resetCounter}
                answerSurveyCustomerServiceRecords={
                  data.answerSurveyCustomerServiceRecords ?? []
                }
              />
            </div>
          )}
        </>
      )}
      <div>
        <Button
          className="cui_button intervention_popup__submit_button"
          variant="contained"
          onClick={handleSubmit}
          disabled={
            formValidator.isSubmitDisabled ||
            (isCommissioningCsr && surveyFormData?.isSubmitDisabled)
          }
          data-cy="intervention-popup-submit"
        >
          {t("intervention_popup.submit")}
        </Button>
      </div>
    </Dialog>
  );
}
