import React, { useEffect } from "react";
import "./CsrForm.css";
import { useTranslation } from "react-i18next";
import { Button } from "@mui/material";
import { useFormApiAutoCompleteDropdown } from "../../hooks/useFormApiAutoCompleteDropdown";
import { IDropdownItem } from "../../@type/IDropdownItem";
import FormApiAutoCompleteDropdown from "../FormApiAutoCompleteDropdown/FormApiAutoCompleteDropdown";
import { useFormField } from "../../hooks/useFormField";
import FormField from "../FormField/FormField";
import { useFormDatePicker } from "../../hooks/useFormDatePicker";
import FormDatePicker from "../FormDatePicker/FormDatePicker";
import { fetchAirport } from "../../utils/dropdown/airport";
import { fetchCsrPeopleOptions } from "../../utils/dropdown/people";
import FormRichTextField from "../FormRichTextField/FormRichTextField";
import { useFormRichTextField } from "../../hooks/useFormRichTextField";
import { useFormValidator } from "../../hooks/useFormValidator";
import { ICsrFormData } from "../../@type/ICsrFormData";
import { useFormSingleSelectDropdown } from "../../hooks/useFormSingleSelectDropdown";
import FormSingleSelectDropdown from "../FormSingleSelectDropdown/FormSingleSelectDropdown";
import { useAppDispatch } from "../../hooks/hooks";
import { fetchCsrContactOptions } from "../../utils/dropdown/csrContact";
import { IEquipmentRecord } from "../../@type/IGetCustomerServiceRecordResponse";

const validateStatus = (value: IDropdownItem | null, optional: boolean) => {
  if (value || optional) return "";
  return "csr_form.status.error.required";
};

interface IProps {
  airport?: IDropdownItem | null;
  title?: string;
  description?: string;
  serviceTechnician?: IDropdownItem | null;
  plannedDate?: string | null;
  status?: IDropdownItem | null;
  contact?: IDropdownItem | null;
  onSubmit: (data: ICsrFormData) => void;
  statusDropdownItemList?: Array<IDropdownItem>;
  showServiceTechnician?: boolean;
  showPlannedDate?: boolean;
  showStatus?: boolean;
  equipmentRecord?: IEquipmentRecord | null;
}

export default function CsrForm(props: IProps) {
  const {
    airport: airportDefault = null,
    title: titleDefault = "",
    description: descriptionDefault = "",
    serviceTechnician: serviceTechnicianDefault = null,
    plannedDate: plannedDateDefault = null,
    status: statusDefault = null,
    contact: contactDefault = null,
    onSubmit,
    statusDropdownItemList = [],
    showServiceTechnician = false,
    showPlannedDate = false,
    showStatus = false,
    equipmentRecord = null,
  } = props;
  const { t } = useTranslation();
  const dispatch = useAppDispatch();

  const airport = useFormApiAutoCompleteDropdown({
    defaultValue: airportDefault,
    fetchData: fetchAirport,
    requiredError: "csr_form.airport.error.required",
  });

  const title = useFormField({
    defaultValue: titleDefault,
    requiredError: "csr_form.title.error.required",
  });

  const description = useFormRichTextField({
    defaultValue: descriptionDefault,
    requiredError: "csr_form.description.error.required",
  });

  const serviceTechnician = useFormSingleSelectDropdown({
    defaultValue: serviceTechnicianDefault,
    list: [],
    fetchOptions: fetchCsrPeopleOptions,
    selector: (state) => state.dropdownOption.csrPeople,
  });

  const plannedDate = useFormDatePicker({
    defaultValue: plannedDateDefault,
  });

  const status = useFormSingleSelectDropdown({
    defaultValue: statusDefault,
    list: statusDropdownItemList,
    validate: (value) => validateStatus(value, showStatus),
  });

  const contact = useFormSingleSelectDropdown({
    defaultValue: contactDefault,
    list: [],
    selector: (state) => state.dropdownOption.csrContact,
  });

  const formValidator = useFormValidator([
    airport,
    title,
    description,
    serviceTechnician,
    plannedDate,
  ]);

  const handleSubmit = () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      onSubmit({
        airport: airport.value,
        title: title.value,
        description: description.value,
        serviceTechnician: serviceTechnician.value,
        plannedDate: plannedDate.formattedValue,
        status: status.value,
        contact: contact.value,
      });
    }
  };

  useEffect(() => {
    if (equipmentRecord) {
      dispatch(fetchCsrContactOptions(equipmentRecord));
    }
  }, [JSON.stringify(equipmentRecord)]);

  return (
    <div className="csr_form__wrapper">
      <FormApiAutoCompleteDropdown
        {...airport.fieldProps}
        label="csr_form.airport.title"
        requiredLabel
        id="airport"
        placeholder="csr_form.airport.placeholder"
        dataCy="csr-form-airport"
      />
      <FormField
        {...title.fieldProps}
        label="csr_form.title.title"
        requiredLabel
        id="title"
        placeholder="csr_form.title.placeholder"
        dataCy="csr-form-title"
      />
      <FormRichTextField
        label="csr_form.description.title"
        requiredLabel
        {...description.fieldProps}
        placeholder="csr_form.description.placeholder"
        dataCy="csr-form-description"
      />
      {showServiceTechnician && (
        <FormSingleSelectDropdown
          {...serviceTechnician.fieldProps}
          label="csr_form.service_technician.title"
          placeholder="csr_form.service_technician.placeholder"
          dataCy="csr-form-service-technician"
        />
      )}
      {showPlannedDate && (
        <FormDatePicker
          {...plannedDate.fieldProps}
          label="csr_form.planned_date.title"
          dataCy="csr-form-planned-date"
        />
      )}
      <FormSingleSelectDropdown
        {...contact.fieldProps}
        label="csr_form.contact.title"
        placeholder="csr_form.contact.placeholder"
        dataCy="csr-form-contact"
      />
      {showStatus && (
        <FormSingleSelectDropdown
          {...status.fieldProps}
          label="csr_form.status.title"
          requiredLabel
          placeholder="csr_form.status.placeholder"
          dataCy="csr-form-status"
        />
      )}
      <Button
        disabled={formValidator.isSubmitDisabled}
        className="cui_button csr_form__submit"
        type="submit"
        variant="contained"
        onClick={handleSubmit}
        data-cy="csr-form-submit"
      >
        {t("csr_form.submit")}
      </Button>
    </div>
  );
}
