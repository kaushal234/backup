import React from "react";
import "./CsrStatusPopUp.css";
import { Button, Dialog, Typography } from "@mui/material";
import { t } from "i18next";
import { StatusCodes } from "http-status-codes";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { useFormSingleSelectDropdown } from "../../hooks/useFormSingleSelectDropdown";
import FormSingleSelectDropdown from "../FormSingleSelectDropdown/FormSingleSelectDropdown";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { IDB_DATABASE } from "../../constants/constants";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { toastError } from "../../utils/api";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import { useFormValidator } from "../../hooks/useFormValidator";
import { IGetCustomerServiceRecordResponse } from "../../@type/IGetCustomerServiceRecordResponse";
import {
  IPutCustomerServiceRecordApiPayload,
  putCustomerServiceRecord,
} from "../../api/putCustomerServiceRecord";
import { refreshCsrData } from "../../redux/slices/csrDetailSlice";
import { createCsrStatusDropdownItem } from "../../utils/dropdown/csrStatus";

interface IProps {
  data: IGetCustomerServiceRecordResponse;
  isOpen: boolean;
  onClose: () => void;
}

export default function CsrStatusPopUp(props: IProps) {
  const { data, isOpen, onClose } = props;
  const dispatch = useAppDispatch();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const status = useFormSingleSelectDropdown({
    defaultValue: null,
    list: (data.availableStatus ?? []).map((item) =>
      createCsrStatusDropdownItem(item)
    ),
    requiredError: "csr_status_popup.status.error.required",
  });

  const handleClose = () => {
    onClose();
    status.reset();
  };

  const formValidator = useFormValidator([status]);

  const handleSubmit = async () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      const params: IPutCustomerServiceRecordApiPayload = {
        "@id": data["@id"],
        data: {
          status: status.value?.id ?? "",
        },
      };
      if (isOnline) {
        dispatch(showMainLoader(true));
        const response = await putCustomerServiceRecord(params);
        if (response.status === StatusCodes.OK && response.data) {
          dispatch(setToastMessage("csr_update.success"));
          dispatch(refreshCsrData());
        } else {
          toastError(dispatch, response);
          dispatch(showMainLoader(false));
        }
      } else {
        await registerSyncEventWithApiPayload({
          eventName: IDB_DATABASE.stores.sync_put_csr,
          payload: params,
        });
        dispatch(setToastMessage("csr_update.offline"));
      }
      handleClose();
    }
  };

  return (
    <Dialog
      closeAfterTransition={false}
      className="csr_status_popup__wrapper"
      onClose={() => handleClose()}
      open={isOpen}
    >
      <Typography
        className="cui_light_text"
        variant="h5"
        gutterBottom
        data-cy="csr-status-popup-heading"
      >
        {t("csr_status_popup.heading")}
      </Typography>
      <div className="csr_status_popup__input_wrapper">
        <FormSingleSelectDropdown
          {...status.fieldProps}
          label="csr_status_popup.status.title"
          requiredLabel
          placeholder="csr_status_popup.status.placeholder"
          dataCy="csr-status-popup-status"
        />
      </div>
      <div>
        <Button
          className="cui_button csr_status_popup__submit_button"
          variant="contained"
          onClick={handleSubmit}
          disabled={formValidator.isSubmitDisabled}
          data-cy="csr-status-popup-submit"
        >
          {t("csr_status_popup.submit")}
        </Button>
      </div>
    </Dialog>
  );
}
