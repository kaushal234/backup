import React, { useEffect, useState } from "react";
import "./ContactPopUp.css";
import { Button, Dialog, Typography } from "@mui/material";
import { t } from "i18next";
import { StatusCodes } from "http-status-codes";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { useFormSingleSelectDropdown } from "../../hooks/useFormSingleSelectDropdown";
import FormSingleSelectDropdown from "../FormSingleSelectDropdown/FormSingleSelectDropdown";
import { useFormField } from "../../hooks/useFormField";
import FormField from "../FormField/FormField";
import { useFormValidator } from "../../hooks/useFormValidator";
import { useFormApiAutoCompleteDropdown } from "../../hooks/useFormApiAutoCompleteDropdown";
import FormApiAutoCompleteDropdown from "../FormApiAutoCompleteDropdown/FormApiAutoCompleteDropdown";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { fetchCountry } from "../../utils/dropdown/country";
import { fetchCustomerRelationshipTeam } from "../../utils/dropdown/customerRelationshipTeam";
import {
  IDB_DATABASE,
  LANGUAGES_OPTIONS,
  PHONE_FORMAT,
} from "../../constants/constants";
import {
  IPostExtranetUserApiPayload,
  postExtranetUser,
} from "../../api/postExtranetUser";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { toastError } from "../../utils/api";
import { fetchContactOptions } from "../../utils/dropdown/contact";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";

const validatePhone = (value: string) => {
  if (!value) {
    return "contact_popup.phone_number.error.required";
  }
  if (!PHONE_FORMAT.test(value)) {
    return "contact_popup.phone_number.error.format";
  }
  return "";
};

interface IProps {
  customer: string;
  isOpen: boolean;
  onClose: () => void;
}

export default function ContactPopUp(props: IProps) {
  const { customer, isOpen, onClose } = props;
  const dispatch = useAppDispatch();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const [crtList, setCrtList] = useState<Array<IDropdownItem>>([]);

  const crt = useFormSingleSelectDropdown({
    defaultValue: null,
    list: crtList,
    requiredError: "contact_popup.crt.error.required",
  });

  const email = useFormField({
    defaultValue: "",
    requiredError: "contact_popup.email.error.required",
  });

  const lastname = useFormField({
    defaultValue: "",
    requiredError: "contact_popup.lastname.error.required",
  });

  const firstname = useFormField({
    defaultValue: "",
    requiredError: "contact_popup.firstname.error.required",
  });

  const division = useFormField({
    defaultValue: "",
    requiredError: "contact_popup.division.error.required",
  });

  const department = useFormField({
    defaultValue: "",
    requiredError: "contact_popup.department.error.required",
  });

  const jobTitle = useFormField({
    defaultValue: "",
    requiredError: "contact_popup.job_title.error.required",
  });

  const phoneNumber = useFormField({
    defaultValue: "",
    validate: validatePhone,
  });

  const language = useFormSingleSelectDropdown({
    defaultValue: null,
    list: LANGUAGES_OPTIONS,
  });

  const country = useFormApiAutoCompleteDropdown({
    defaultValue: null,
    fetchData: fetchCountry,
    requiredError: "contact_popup.country.error.required",
  });

  const handleClose = () => {
    onClose();
    crt.reset();
    email.reset();
    lastname.reset();
    firstname.reset();
    division.reset();
    department.reset();
    jobTitle.reset();
    phoneNumber.reset();
    language.reset();
    country.reset();
  };

  const formValidator = useFormValidator([
    crt,
    email,
    lastname,
    firstname,
    division,
    department,
    jobTitle,
    phoneNumber,
    language,
    country,
  ]);

  const handleSubmit = async () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      const params: IPostExtranetUserApiPayload = {
        extranetUser: {
          firstname: firstname.value,
          lastname: lastname.value,
          email: email.value,
          username: email.value,
          phones: [
            {
              type: "phone",
              number: phoneNumber.value,
            },
          ],
          disabled: false,
          extranetUserProfile: {
            country: country.value?.id ?? "",
            department: department.value,
            division: division.value,
            jobTitle: jobTitle.value,
            language: language.value?.id ?? "",
          },
        },
        customerRelationshipTeam: crt.value?.id ?? "",
        groupName: "role_ST",
      };

      if (isOnline) {
        dispatch(showMainLoader(true));
        const response = await postExtranetUser(params);
        if (response.status === StatusCodes.CREATED && response.data) {
          await dispatch(fetchContactOptions({ customer }));
          dispatch(setToastMessage("contact_popup.success"));
        } else {
          toastError(dispatch, response);
        }
      } else {
        await registerSyncEventWithApiPayload({
          eventName: IDB_DATABASE.stores.sync_post_extranet_user,
          payload: params,
        });
        dispatch(setToastMessage("contact_popup.offline"));
      }
      handleClose();
      dispatch(showMainLoader(false));
    }
  };

  const fetchCrtList = async () => {
    setCrtList(await fetchCustomerRelationshipTeam(customer));
  };

  useEffect(() => {
    fetchCrtList();
  }, [customer]);

  return (
    <Dialog
      closeAfterTransition={false}
      className="contact_popup__wrapper"
      onClose={() => handleClose()}
      open={isOpen}
    >
      <Typography
        className="cui_light_text"
        variant="h5"
        gutterBottom
        data-cy="contact-popup-heading"
      >
        {t("contact_popup.heading")}
      </Typography>
      <div className="contact_popup__input_wrapper">
        <FormSingleSelectDropdown
          {...crt.fieldProps}
          label="contact_popup.crt.label"
          requiredLabel
          placeholder="contact_popup.crt.placeholder"
          dataCy="contact-popup-crt"
        />
      </div>
      <div className="contact_popup__input_wrapper">
        <FormField
          {...email.fieldProps}
          label="contact_popup.email.label"
          id="email"
          placeholder="contact_popup.email.placeholder"
          dataCy="contact-popup-email"
          requiredLabel
        />
      </div>
      <div className="contact_popup__input_wrapper">
        <FormField
          {...lastname.fieldProps}
          label="contact_popup.lastname.label"
          id="lastname"
          placeholder="contact_popup.lastname.placeholder"
          dataCy="contact-popup-last-name"
          requiredLabel
        />
      </div>
      <div className="contact_popup__input_wrapper">
        <FormField
          {...firstname.fieldProps}
          label="contact_popup.firstname.label"
          id="firstname"
          placeholder="contact_popup.firstname.placeholder"
          dataCy="contact-popup-first-name"
          requiredLabel
        />
      </div>
      <div className="contact_popup__input_wrapper">
        <FormField
          {...division.fieldProps}
          label="contact_popup.division.label"
          id="division"
          placeholder="contact_popup.division.placeholder"
          dataCy="contact-popup-division"
          requiredLabel
        />
      </div>
      <div className="contact_popup__input_wrapper">
        <FormField
          {...department.fieldProps}
          label="contact_popup.department.label"
          id="department"
          placeholder="contact_popup.department.placeholder"
          dataCy="contact-popup-department"
          requiredLabel
        />
      </div>
      <div className="contact_popup__input_wrapper">
        <FormField
          {...jobTitle.fieldProps}
          label="contact_popup.job_title.label"
          id="job-title"
          placeholder="contact_popup.job_title.placeholder"
          dataCy="contact-popup-job-title"
          requiredLabel
        />
      </div>
      <div className="contact_popup__input_wrapper">
        <FormField
          {...phoneNumber.fieldProps}
          label="contact_popup.phone_number.label"
          id="phone-number"
          placeholder="contact_popup.phone_number.placeholder"
          dataCy="contact-popup-phone-number"
          requiredLabel
        />
      </div>
      <div className="contact_popup__input_wrapper">
        <FormSingleSelectDropdown
          {...language.fieldProps}
          label="contact_popup.language.label"
          placeholder="contact_popup.language.placeholder"
          dataCy="contact-popup-language"
        />
      </div>
      <div className="contact_popup__input_wrapper">
        <FormApiAutoCompleteDropdown
          {...country.fieldProps}
          label="contact_popup.country.label"
          id="country"
          placeholder="contact_popup.country.placeholder"
          dataCy="contact-popup-country"
          requiredLabel
        />
      </div>

      <div>
        <Button
          className="cui_button contact_popup__submit_button"
          variant="contained"
          onClick={handleSubmit}
          disabled={formValidator.isSubmitDisabled}
          data-cy="contact-popup-submit"
        >
          {t("contact_popup.submit")}
        </Button>
      </div>
    </Dialog>
  );
}
