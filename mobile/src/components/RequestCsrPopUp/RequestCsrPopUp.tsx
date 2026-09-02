import React from "react";
import "./RequestCsrPopUp.css";
import { Button, Dialog, Typography } from "@mui/material";
import { t } from "i18next";
import { StatusCodes } from "http-status-codes";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { ITechnicianOnCall } from "../../@type/IGetTechnicalOnCallsResponse";
import { useFormValidator } from "../../hooks/useFormValidator";
import { useFormApiAutoCompleteDropdown } from "../../hooks/useFormApiAutoCompleteDropdown";
import { useFormDatePicker } from "../../hooks/useFormDatePicker";
import {
  createPeopleDropdownItem,
  fetchPeople,
} from "../../utils/dropdown/people";
import FormApiAutoCompleteDropdown from "../FormApiAutoCompleteDropdown/FormApiAutoCompleteDropdown";
import FormDatePicker from "../FormDatePicker/FormDatePicker";
import {
  IPutRequestTechnicianApiPayload,
  putRequestTechnician,
} from "../../api/putRequestTechnician";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { refreshTocData } from "../../redux/slices/tocDetailSlice";
import { toastError } from "../../utils/api";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import { IDB_DATABASE } from "../../constants/constants";
import { getFormattedTodayDate } from "../../utils/date";

interface IProps {
  data: ITechnicianOnCall;
  isOpen: boolean;
  onClose: () => void;
}

export default function RequestCsrPopUp(props: IProps) {
  const { data, isOpen, onClose } = props;
  const dispatch = useAppDispatch();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);
  const userInfo = useAppSelector((state) => state.auth.userInfo);

  const serviceTechnician = useFormApiAutoCompleteDropdown({
    defaultValue: userInfo ? createPeopleDropdownItem(userInfo) : null,
    fetchData: fetchPeople,
    requiredError: "toc_form.service_technician.error.required",
  });

  const plannedDate = useFormDatePicker({
    defaultValue: getFormattedTodayDate(),
  });

  const handleClose = () => {
    onClose();
    serviceTechnician.reset();
    plannedDate.reset();
  };

  const formValidator = useFormValidator([serviceTechnician, plannedDate]);

  const handleSubmit = async () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      const params: IPutRequestTechnicianApiPayload = {
        id: data.id?.toString() ?? "",
        data: {
          nestedCustomerServiceRecord: {
            leader: serviceTechnician.value?.id ?? null,
            plannedAt: plannedDate.formattedValue,
          },
        },
      };
      if (isOnline) {
        dispatch(showMainLoader(true));
        const response = await putRequestTechnician(params);
        const csrError =
          response.data?.["@sub_resources"]?.customerServiceRecord.detail;
        if (response.status === StatusCodes.OK && response.data) {
          dispatch(setToastMessage("request_csr.success"));
          dispatch(refreshTocData());
        } else if (csrError) {
          dispatch(setToastMessage(csrError));
          dispatch(showMainLoader(false));
        } else {
          toastError(dispatch, response);
          dispatch(showMainLoader(false));
        }
      } else {
        await registerSyncEventWithApiPayload({
          eventName: IDB_DATABASE.stores.sync_put_request_technician,
          payload: params,
        });
        dispatch(setToastMessage("request_csr.offline"));
      }
      handleClose();
    }
  };

  return (
    <Dialog
      closeAfterTransition={false}
      className="request_csr_popup__wrapper"
      onClose={() => handleClose()}
      open={isOpen}
    >
      <Typography
        className="cui_light_text"
        variant="h5"
        gutterBottom
        data-cy="toc-status-popup-heading"
      >
        {t("request_csr.title")}
      </Typography>
      <div className="request_csr_popup__input_wrapper">
        <FormApiAutoCompleteDropdown
          {...serviceTechnician.fieldProps}
          label="request_csr.service_technician.title"
          requiredLabel
          id="service-technician"
          placeholder="request_csr.service_technician.placeholder"
          dataCy="request-csr-popup-service-technician"
        />
      </div>
      <div className="request_csr_popup__input_wrapper">
        <FormDatePicker
          {...plannedDate.fieldProps}
          label="request_csr.planned_date.title"
          dataCy="request-csr-popup-planned-date"
        />
      </div>
      <div>
        <Button
          className="cui_button request_csr_popup__submit_button"
          variant="contained"
          onClick={handleSubmit}
          disabled={formValidator.isSubmitDisabled}
          data-cy="request-csr-popup-submit"
        >
          {t("request_csr.submit")}
        </Button>
      </div>
    </Dialog>
  );
}
