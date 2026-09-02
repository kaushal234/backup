import React, { useState } from "react";
import "./TocStatusPopUp.css";
import { Button, Dialog, Typography } from "@mui/material";
import { t } from "i18next";
import { StatusCodes } from "http-status-codes";
import InfoOutlinedIcon from "@mui/icons-material/InfoOutlined";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { createTocStatusDropdownItem } from "../../utils/dropdown/tocStatus";
import { useFormSingleSelectDropdown } from "../../hooks/useFormSingleSelectDropdown";
import FormSingleSelectDropdown from "../FormSingleSelectDropdown/FormSingleSelectDropdown";
import {
  IPutTechnicianOnCallStatusApiPayload,
  putTechnicianOnCallStatus,
} from "../../api/putTechnicianOnCallStatus";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { IDB_DATABASE, TOC_STATUS } from "../../constants/constants";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { toastError } from "../../utils/api";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import { refreshTocData } from "../../redux/slices/tocDetailSlice";
import { useFormField } from "../../hooks/useFormField";
import FormField from "../FormField/FormField";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { ITechnicianOnCall } from "../../@type/IGetTechnicalOnCallsResponse";
import { useFormValidator } from "../../hooks/useFormValidator";
import {
  IPostCommentByTocIdApiPayload,
  postCommentById,
} from "../../api/postCommentById";
import TocStatusPartsTable from "../TocStatusPartsTable/TocStatusPartsTable";
import { ITocDefectivePart } from "../../@type/ITocDefectivePart";

const validateSymptoms = (value: string, isRequired: boolean) => {
  if (value || !isRequired) return "";
  return "toc_details.status_popup.symptoms.error.required";
};

const validateRootCause = (value: string, isRequired: boolean) => {
  if (value || !isRequired) return "";
  return "toc_details.status_popup.root_cause.error.required";
};

const validateSolution = (value: string, isRequired: boolean) => {
  if (value || !isRequired) return "";
  return "toc_details.status_popup.solution.error.required";
};

const validateReason = (value: string, isStatusSuspended: boolean) => {
  if (value || !isStatusSuspended) return "";
  return "toc_details.status_popup.reason.error.required";
};

const validateThirdPartyJobDescription = (
  value: string,
  newStatus: string,
  thirdPartyName: string
) => {
  if (newStatus !== "SOLVED" || value || !thirdPartyName) return "";
  return "toc_details.status_popup.third_party_job_description.error.required";
};

const validateThirdPartyHours = (
  value: string,
  newStatus: string,
  thirdPartyName: string
) => {
  if (newStatus !== "SOLVED" || !thirdPartyName) return "";
  if (!value) {
    return "toc_details.status_popup.third_party_hours.error.required";
  }
  if (+value < 1) {
    return "toc_details.status_popup.third_party_hours.error.zero";
  }
  return "";
};

const validateStatus = (
  value: IDropdownItem | null,
  data: ITechnicianOnCall
) => {
  if (value) {
    if (value.id === TOC_STATUS.SOLVED) {
      if (data.currentCustomerServiceRecord?.closed === false) {
        return "toc_update.error.csr";
      }
      if (data.factoryFlag) {
        return "toc_update.error.factory_flag";
      }
    }
    return "";
  }
  return "toc_details.status_popup.status.error.required";
};

interface IProps {
  data: ITechnicianOnCall;
  isOpen: boolean;
  onClose: () => void;
}

export default function TocStatusPopUp(props: IProps) {
  const { data, isOpen, onClose } = props;
  const dispatch = useAppDispatch();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const isMigratedToc =
    data.status === TOC_STATUS.SOLVED &&
    (!data.originalSymptoms ||
      !data.originalRootCause ||
      !data.originalSolution);

  const [defectiveParts, setDefectiveParts] = useState<
    Array<ITocDefectivePart>
  >([]);

  const status = useFormSingleSelectDropdown({
    defaultValue: null,
    list: (data.availableStatus ?? []).map((item) =>
      createTocStatusDropdownItem(item)
    ),
    validate: (value) => validateStatus(value, data),
  });

  const isStatusClosed = status.value?.id === TOC_STATUS.CLOSED;
  const isStatusSolved = status.value?.id === TOC_STATUS.SOLVED;
  const isStatusSuspended = status.value?.id === TOC_STATUS.SUSPENDED;

  const isSolvingInfoRequired =
    isStatusSolved || (isStatusClosed && isMigratedToc);

  const symptoms = useFormField({
    defaultValue: data.originalSymptoms || data.originalTitle || data.title,
    validate: (newValue) => validateSymptoms(newValue, isSolvingInfoRequired),
  });

  const rootCause = useFormField({
    defaultValue: data.originalRootCause ?? "",
    validate: (newValue) => validateRootCause(newValue, isSolvingInfoRequired),
  });

  const solution = useFormField({
    defaultValue: data.originalSolution ?? "",
    validate: (newValue) => validateSolution(newValue, isSolvingInfoRequired),
  });

  const reason = useFormField({
    defaultValue: "",
    validate: (newValue) => validateReason(newValue, isStatusSuspended),
  });

  const thirdPartyJobDescription = useFormField({
    defaultValue: data.thirdPartyJobDescription ?? "",
    validate: (newValue) =>
      validateThirdPartyJobDescription(
        newValue,
        status.value?.id ?? "",
        data.thirdPartyName ?? ""
      ),
    dependsOn: [status.value],
  });

  const thirdPartyHours = useFormField({
    defaultValue: data.thirdPartyHours?.toString() ?? "",
    validate: (newValue) =>
      validateThirdPartyHours(
        newValue,
        status.value?.id ?? "",
        data.thirdPartyName ?? ""
      ),
    dependsOn: [status.value],
    isNumber: true,
  });

  const handleClose = () => {
    onClose();
    status.reset();
    symptoms.reset();
    rootCause.reset();
    solution.reset();
  };

  const formValidator = useFormValidator([
    status,
    symptoms,
    rootCause,
    solution,
    reason,
    thirdPartyJobDescription,
    thirdPartyHours,
  ]);

  const handleSubmit = async () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      const statusParams: IPutTechnicianOnCallStatusApiPayload = {
        id: data.id?.toString() ?? "",
        data: {
          status: status.value?.id ?? "",
          ...(isSolvingInfoRequired && {
            originalSymptoms: symptoms.value,
            originalRootCause: rootCause.value,
            originalSolution: solution.value,
          }),
          ...(isStatusSolved && { defectiveParts }),
          ...(thirdPartyJobDescription.value && {
            thirdPartyJobDescription: thirdPartyJobDescription.value,
          }),
          ...(thirdPartyHours.value && {
            thirdPartyHours: +thirdPartyHours.value,
          }),
        },
      };
      const commentParams: IPostCommentByTocIdApiPayload = {
        "@id": data["@id"],
        comment: reason.value,
        public: true,
      };
      if (isOnline) {
        dispatch(showMainLoader(true));
        const response = await putTechnicianOnCallStatus(statusParams);
        if (isStatusSuspended) {
          await postCommentById(commentParams);
        }
        if (response.status === StatusCodes.OK && response.data) {
          dispatch(setToastMessage("toc_update.success"));
          dispatch(refreshTocData());
        } else {
          toastError(dispatch, response);
          dispatch(showMainLoader(false));
        }
      } else {
        await registerSyncEventWithApiPayload({
          eventName: IDB_DATABASE.stores.sync_put_toc_status,
          payload: statusParams,
        });
        if (isStatusSuspended) {
          await registerSyncEventWithApiPayload({
            eventName: IDB_DATABASE.stores.sync_post_comment,
            payload: commentParams,
          });
        }
        dispatch(setToastMessage("toc_update.offline"));
      }
      handleClose();
    }
  };

  return (
    <Dialog
      closeAfterTransition={false}
      className="toc_status_popup__wrapper"
      onClose={() => handleClose()}
      open={isOpen}
    >
      <Typography
        className="cui_light_text"
        variant="h5"
        gutterBottom
        data-cy="toc-status-popup-heading"
      >
        {t("toc_details.status_popup.heading")}
      </Typography>
      {data.statusBlockerMessage?.length === 0 &&
        data.availableStatus?.length === 0 && (
          <div className="toc_status_popup__message_wrapper">
            <div className="toc_status_popup__solved_warning">
              <InfoOutlinedIcon fontSize="small" />
              <Typography variant="caption" gutterBottom>
                {t("toc_details.status_popup.access_error")}
              </Typography>
            </div>
          </div>
        )}
      {data.statusBlockerMessage?.length !== 0 && (
        <div className="toc_status_popup__message_wrapper">
          {(data.statusBlockerMessage ?? []).map((message) => (
            <div
              className="toc_status_popup__solved_warning"
              key={message.message}
            >
              <InfoOutlinedIcon fontSize="small" />
              <Typography variant="caption" gutterBottom>
                {message.message}
              </Typography>
            </div>
          ))}
        </div>
      )}
      {data.availableStatus?.length !== 0 && (
        <>
          <div className="toc_status_popup__input_wrapper">
            <FormSingleSelectDropdown
              {...status.fieldProps}
              label="toc_details.status_popup.status.title"
              requiredLabel
              placeholder="toc_details.status_popup.status.placeholder"
              dataCy="toc-status-popup-status"
            />
          </div>
          {isSolvingInfoRequired && (
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
            </>
          )}
          {isStatusSolved && (
            <>
              {data.thirdPartyName && (
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
                  data={data}
                  dataCy="toc-status"
                  setDefectiveParts={setDefectiveParts}
                />
              </div>
            </>
          )}
          {status.value?.id === TOC_STATUS.SUSPENDED && (
            <div className="toc_status_popup__input_wrapper">
              <FormField
                {...reason.fieldProps}
                label="toc_details.status_popup.reason.title"
                id="reason"
                placeholder="toc_details.status_popup.reason.placeholder"
                dataCy="toc-status-popup-reason"
                requiredLabel
                rows={4}
              />
            </div>
          )}
          <div>
            <Button
              className="cui_button toc_status_popup__submit_button"
              variant="contained"
              onClick={handleSubmit}
              disabled={formValidator.isSubmitDisabled}
              data-cy="toc-status-popup-submit"
            >
              {t("toc_details.status_popup.submit")}
            </Button>
          </div>
        </>
      )}
    </Dialog>
  );
}
