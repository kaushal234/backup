import React from "react";
import "./UpdateUnitOperationalStatusPopUp.css";
import { Button, Dialog, Typography } from "@mui/material";
import { t } from "i18next";
import { StatusCodes } from "http-status-codes";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { ITechnicianOnCall } from "../../@type/IGetTechnicalOnCallsResponse";
import { useFormValidator } from "../../hooks/useFormValidator";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import {
  setToastMessage,
  setToastMessageWithParams,
} from "../../redux/slices/toastSlice";
import { refreshTocData } from "../../redux/slices/tocDetailSlice";
import { toastError } from "../../utils/api";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import {
  IDB_DATABASE,
  TOC_FILTER_IFACTOR_OPTIONS,
  TOC_IFACTOR,
  UNIT_OPERATIONAL_STATUS,
} from "../../constants/constants";
import { useFormSingleSelectDropdown } from "../../hooks/useFormSingleSelectDropdown";
import {
  createUnitOperationalStatusDropdownItem,
  fetchUnitOperationalStatusOptions,
} from "../../utils/dropdown/unitOperationalStatus";
import {
  IPutTechnicianOnCallApiPayload,
  putTechnicianOnCall,
} from "../../api/putTechnicianOnCall";
import FormSingleSelectDropdown from "../FormSingleSelectDropdown/FormSingleSelectDropdown";
import { validateIfactor } from "../TocForm/TocForm";

interface IProps {
  data: ITechnicianOnCall;
  isOpen: boolean;
  onClose: () => void;
}

export default function UpdateUnitOperationalStatusPopUp(props: IProps) {
  const { data, isOpen, onClose } = props;
  const dispatch = useAppDispatch();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const unitOperationalStatusDefault = data.unitOperationalStatus
    ? createUnitOperationalStatusDropdownItem(data.unitOperationalStatus)
    : null;

  const unitOperationalStatus = useFormSingleSelectDropdown({
    defaultValue: unitOperationalStatusDefault,
    list: [],
    fetchOptions: fetchUnitOperationalStatusOptions,
    selector: (state) => state.dropdownOption.unitOperationalStatus,
    requiredError: "toc_form.unit_operational_status.error.required",
  });

  const ifactorDefault =
    TOC_FILTER_IFACTOR_OPTIONS.find((item) => item.id === data.indiceFactor) ??
    null;

  const ifactor = useFormSingleSelectDropdown({
    defaultValue: ifactorDefault,
    list: TOC_FILTER_IFACTOR_OPTIONS,
    validate: (value) => validateIfactor(value, unitOperationalStatus.value),
    dependsOn: [unitOperationalStatus.value],
  });

  const showIFactor =
    data.indiceFactor === TOC_IFACTOR.IF_1 &&
    unitOperationalStatus.value?.id !== UNIT_OPERATIONAL_STATUS.MCF;

  const handleClose = () => {
    onClose();
    unitOperationalStatus.reset();
  };

  const formValidator = useFormValidator([unitOperationalStatus, ifactor]);

  const handleSubmit = async () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      const params: IPutTechnicianOnCallApiPayload = {
        id: data.id?.toString() ?? "",
        data: {
          unitOperationalStatus: unitOperationalStatus.value?.id ?? "",
          ...(showIFactor && { indiceFactor: ifactor.value?.id ?? "" }),
        },
      };
      if (isOnline) {
        dispatch(showMainLoader(true));
        const response = await putTechnicianOnCall(params);

        if (response.status === StatusCodes.OK && response.data) {
          setToastMessageWithParams({
            toastMessage: "toc_update.success",
            params: { tocId: `${response.data.id}` },
          });
          dispatch(refreshTocData());
        } else {
          toastError(dispatch, response);
          dispatch(showMainLoader(false));
        }
      } else {
        await registerSyncEventWithApiPayload({
          eventName: IDB_DATABASE.stores.sync_put_toc,
          payload: params,
        });
        dispatch(setToastMessage("toc_update.offline"));
      }
      handleClose();
    }
  };

  return (
    <Dialog
      closeAfterTransition={false}
      className="update_unit_operational_status_popup__wrapper"
      onClose={() => handleClose()}
      open={isOpen}
    >
      <Typography
        className="cui_light_text"
        variant="h5"
        gutterBottom
        data-cy="unit-operational-status-popup-heading"
      >
        {t("toc_details.unit_operational_status_popup.heading")}
      </Typography>
      <div className="update_unit_operational_status_popup__input_wrapper">
        <FormSingleSelectDropdown
          {...unitOperationalStatus.fieldProps}
          label="toc_form.unit_operational_status.title"
          requiredLabel
          placeholder="toc_form.unit_operational_status.placeholder"
          dataCy="update-unit-operational-status-form-unit-operational-status"
        />
      </div>
      {showIFactor && (
        <div className="update_unit_operational_status_popup__input_wrapper">
          <FormSingleSelectDropdown
            {...ifactor.fieldProps}
            label="toc_form.ifactor.title"
            requiredLabel
            placeholder="toc_form.ifactor.placeholder"
            dataCy="update-unit-operational-status-form-ifactor"
          />
        </div>
      )}
      <div>
        <Button
          className="cui_button update_unit_operational_status_popup__submit_button"
          variant="contained"
          onClick={handleSubmit}
          disabled={formValidator.isSubmitDisabled}
          data-cy="update-unit-operational-status-form-submit"
        >
          {t("request_csr.submit")}
        </Button>
      </div>
    </Dialog>
  );
}
