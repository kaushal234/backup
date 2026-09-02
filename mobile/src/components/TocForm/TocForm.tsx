import React, { useEffect, useState } from "react";
import "./TocForm.css";
import { useTranslation } from "react-i18next";
import { Button, Typography } from "@mui/material";
import AddIcon from "@mui/icons-material/Add";
import { useFormApiAutoCompleteDropdown } from "../../hooks/useFormApiAutoCompleteDropdown";
import { IDropdownItem } from "../../@type/IDropdownItem";
import FormApiAutoCompleteDropdown from "../FormApiAutoCompleteDropdown/FormApiAutoCompleteDropdown";
import { useFormMultiSelectDropdown } from "../../hooks/useFormMultiSelectDropdown";
import {
  TOC_FILTER_IFACTOR_OPTIONS,
  TOC_SERVICE_ACTIVITY,
  TOC_UNIT_OPERATIONAL_STATUS_MCF,
} from "../../constants/constants";
import FormMutliSelectDropdown from "../FormMutliSelectDropdown/FormMutliSelectDropdown";
import FormSingleSelectDropdown from "../FormSingleSelectDropdown/FormSingleSelectDropdown";
import { useFormSingleSelectDropdown } from "../../hooks/useFormSingleSelectDropdown";
import { useFormField } from "../../hooks/useFormField";
import FormField from "../FormField/FormField";
import FormSwitch from "../FormSwitch/FormSwitch";
import { useFormSwitch } from "../../hooks/useFormSwitch";
import { useFormDatePicker } from "../../hooks/useFormDatePicker";
import FormDatePicker from "../FormDatePicker/FormDatePicker";
import { useAppDispatch } from "../../hooks/hooks";
import { ITocFormData } from "../../@type/ITocFormData";
import { fetchEquipmentRecordSerialNo } from "../../utils/dropdown/equipmentRecordSerialNumber";
import {
  createAirportDropdownItem,
  fetchAirport,
} from "../../utils/dropdown/airport";
import {
  createServiceOrganisationDropdownItem,
  fetchServiceOrganisationOptions,
} from "../../utils/dropdown/serviceOrganisation";
import { fetchPeople, fetchTechnician } from "../../utils/dropdown/people";
import { fetchServiceActivityOptions } from "../../utils/dropdown/serviceActivity";
import { fetchTechnicianOnCallTagOptions } from "../../utils/dropdown/technicianOnCallTag";
import { fetchTechnicianOnCallTypeOptions } from "../../utils/dropdown/technicianOnCallType";
import { fetchUnitOperationalStatusOptions } from "../../utils/dropdown/unitOperationalStatus";
import FormRichTextField from "../FormRichTextField/FormRichTextField";
import { useFormRichTextField } from "../../hooks/useFormRichTextField";
import FeatureComponent from "../FeatureComponent/FeatureComponent";
import { useFormValidator } from "../../hooks/useFormValidator";
import { fetchContactOptions } from "../../utils/dropdown/contact";
import {
  createCustomerDropdownItem,
  fetchCustomer,
} from "../../utils/dropdown/customer";
import ContactPopUp from "../ContactPopUp/ContactPopUp";
import { fetchErContactOptions } from "../../utils/dropdown/erContact";
import { extractText } from "../../utils/utils";

const validateER = (value: IDropdownItem | null, externalSerial: string) => {
  if (value || externalSerial) return "";
  return "toc_form.er_sn.error.required";
};

export const validateIfactor = (
  ifactor: IDropdownItem | null,
  unitOperationalStatus: IDropdownItem | null
) => {
  if (
    ifactor?.id === "IF 1" &&
    unitOperationalStatus &&
    unitOperationalStatus.id !== "/unit_operational_statuses/MCF"
  ) {
    return "toc_form.ifactor.error.status";
  }
  if (ifactor) return "";
  return "toc_form.ifactor.error.required";
};

const validateServiceTechnician = (
  value: IDropdownItem | null,
  isTechnicianRequested: boolean
) => {
  if (value || !isTechnicianRequested) return "";
  return "toc_form.service_technician.error.required";
};

const validateHourMeter = (newValue: string, oldValue: string) => {
  if (!oldValue || !newValue || +newValue >= +oldValue) return "";
  return "toc_form.hour_meter.error.min_value";
};

const warnServiceOrganisation = (
  value: IDropdownItem | null,
  erValue: IDropdownItem | null
) => {
  if (
    value &&
    erValue?.additionalInfo?.type === "EquipmentRecord" &&
    erValue?.additionalInfo?.data.salesOrganisationService?.["@id"] &&
    erValue.additionalInfo.data.salesOrganisationService["@id"] !== value.id
  ) {
    return "toc_form.service_organisation.warn.sso_mismatch";
  }
  return "";
};

const validateReason = (value: string, isRequired: boolean) => {
  if (isRequired && !value) return "toc_form.reason.error.required";
  return "";
};

const validateTitle = (value: string) => {
  if (value.length >= 12) return "";
  if (!!value && value.length < 12) {
    return "toc_form.title.error.min_chars";
  }
  return "toc_form.title.error.required";
};

const validateDescription = (value: string) => {
  const textValue = extractText(value);
  if (textValue.length >= 12) return "";
  if (!!textValue && textValue.length < 12) {
    return "toc_form.description.error.min_chars";
  }
  return "toc_form.description.error.required";
};

const validateThirdPartyName = (value: string, isRequired: boolean) => {
  return isRequired && !value
    ? "toc_form.third_party_name.error.required_with_ref"
    : "";
};

interface IProps {
  equipmentRecord?: IDropdownItem | null;
  serialNumber?: string;
  mainContact?: IDropdownItem | null;
  contacts?: Array<IDropdownItem>;
  customer?: IDropdownItem | null;
  airport?: IDropdownItem | null;
  assignee?: IDropdownItem | null;
  technician?: IDropdownItem | null;
  ifactor?: IDropdownItem | null;
  errorCodes?: string;
  payer?: IDropdownItem | null;
  serviceActivity?: IDropdownItem | null;
  unitOperationalStatus?: IDropdownItem | null;
  tags?: Array<IDropdownItem>;
  thirdPartyName?: string;
  thirdPartyRef?: string;
  originalTitle?: string;
  originalDescription?: string;
  serviceTechnician?: IDropdownItem | null;
  plannedDate?: string | null;
  serviceOrganisation?: IDropdownItem | null;
  onSubmit: (data: ITocFormData) => void;
  isEdit?: boolean;
  confidential?: boolean;
}

export default function TocForm(props: IProps) {
  const {
    equipmentRecord: equipmentRecordDefault = null,
    serialNumber: serialNumberDefault = "",
    mainContact: mainContactDefault = null,
    contacts: contactsDefault = [],
    customer: customerDefault = null,
    airport: airportDefault = null,
    assignee: assigneeDefault = null,
    technician: technicianDefault = null,
    ifactor: ifactorDefault = null,
    errorCodes: errorCodesDefault = "",
    payer: payerDefault = null,
    serviceActivity: serviceActivityDefault = null,
    unitOperationalStatus: unitOperationalStatusDefault = null,
    tags: tagsDefault = [],
    thirdPartyName: thirdPartyNameDefault = "",
    thirdPartyRef: thirdPartyRefDefault = "",
    originalTitle: originalTitleDefault = "",
    originalDescription: originalDescriptionDefault = "",
    serviceTechnician: serviceTechnicianDefault = null,
    plannedDate: plannedDateDefault = null,
    serviceOrganisation: serviceOrganisationDefault = null,
    onSubmit,
    isEdit = false,
    confidential: confidentialDefault = false,
  } = props;

  const [isContactPopUpVisible, setIsContactPopUpVisible] = useState(false);

  const { t } = useTranslation();
  const dispatch = useAppDispatch();

  const [selectedContacts, setSelectedContacts] = useState<
    Array<IDropdownItem>
  >([]);

  const serialNumber = useFormField({
    defaultValue: serialNumberDefault,
  });

  const equipmentRecordSerialNo = useFormApiAutoCompleteDropdown({
    defaultValue: equipmentRecordDefault,
    fetchData: fetchEquipmentRecordSerialNo,
    validate: (value) => validateER(value, serialNumber.value),
    dependsOn: [serialNumber.value],
  });

  const mainContact = useFormSingleSelectDropdown({
    defaultValue: mainContactDefault,
    list: [],
    selector: (state) => state.dropdownOption.contact,
    excludeItems: selectedContacts,
    requiredError: "toc_form.main_contact.error.required",
  });

  const contacts = useFormMultiSelectDropdown({
    defaultValue: contactsDefault,
    list: [],
    selector: (state) => state.dropdownOption.erContact,
    excludeItems: mainContact.value ? [mainContact.value] : [],
  });

  const customer = useFormApiAutoCompleteDropdown({
    defaultValue: customerDefault,
    fetchData: fetchCustomer,
    requiredError: "toc_form.customer.error.required",
  });

  const airport = useFormApiAutoCompleteDropdown({
    defaultValue: airportDefault,
    fetchData: fetchAirport,
    requiredError: "toc_form.airport.error.required",
  });

  const serviceOrganisation = useFormApiAutoCompleteDropdown({
    defaultValue: serviceOrganisationDefault,
    fetchData: fetchServiceOrganisationOptions,
    requiredError: "toc_form.service_organisation.error.required",
    warn: (value) =>
      warnServiceOrganisation(value, equipmentRecordSerialNo.value),
    dependsOn: [equipmentRecordSerialNo],
  });

  const assignee = useFormApiAutoCompleteDropdown({
    defaultValue: assigneeDefault,
    fetchData: fetchPeople,
    requiredError: "toc_form.assignee.error.required",
  });

  const technician = useFormApiAutoCompleteDropdown({
    defaultValue: technicianDefault,
    fetchData: fetchTechnician,
  });

  const errorCodes = useFormField({
    defaultValue: errorCodesDefault,
  });

  const payer = useFormSingleSelectDropdown({
    defaultValue: payerDefault,
    list: [],
    fetchOptions: fetchTechnicianOnCallTypeOptions,
    selector: (state) => state.dropdownOption.technicianOnCallType,
    requiredError: "toc_form.payer.error.required",
  });

  const serviceActivity = useFormSingleSelectDropdown({
    defaultValue: serviceActivityDefault,
    list: [],
    fetchOptions: fetchServiceActivityOptions,
    selector: (state) => state.dropdownOption.serviceActivity,
    requiredError: "toc_form.service_activity.error.required",
  });

  const unitOperationalStatus = useFormSingleSelectDropdown({
    defaultValue: unitOperationalStatusDefault,
    list: [],
    fetchOptions: fetchUnitOperationalStatusOptions,
    selector: (state) => state.dropdownOption.unitOperationalStatus,
    requiredError: "toc_form.unit_operational_status.error.required",
  });

  const ifactor = useFormSingleSelectDropdown({
    defaultValue: ifactorDefault,
    list: TOC_FILTER_IFACTOR_OPTIONS,
    validate: (value) => validateIfactor(value, unitOperationalStatus.value),
    dependsOn: [unitOperationalStatus.value],
  });

  const tags = useFormMultiSelectDropdown({
    defaultValue: tagsDefault,
    list: [],
    fetchOptions: fetchTechnicianOnCallTagOptions,
    selector: (state) => state.dropdownOption.tocTags,
  });

  let hourMeterOldValue = `0`;
  if (
    equipmentRecordSerialNo.value?.additionalInfo?.type === "EquipmentRecord"
  ) {
    hourMeterOldValue = `${
      equipmentRecordSerialNo.value?.additionalInfo?.data?.hourMeter ?? 0
    }`;
  }

  const hourMeter = useFormField({
    defaultValue: isEdit ? hourMeterOldValue : "",
    isNumber: true,
    validate: (value) => validateHourMeter(value, hourMeterOldValue),
  });

  const thirdPartyRef = useFormField({
    defaultValue: thirdPartyRefDefault,
  });

  const thirdPartyName = useFormField({
    defaultValue: thirdPartyNameDefault,
    validate: (value) => validateThirdPartyName(value, !!thirdPartyRef.value),
    dependsOn: [thirdPartyRef.value],
  });

  const originalTitle = useFormField({
    defaultValue: originalTitleDefault,
    validate: (value) => validateTitle(value),
  });

  const originalDescription = useFormRichTextField({
    defaultValue: originalDescriptionDefault,
    validate: (value) => validateDescription(value),
  });

  const confidential = useFormSwitch({
    defaultValue: confidentialDefault,
  });

  const isReasonRequired = confidential.value !== confidentialDefault;

  const reason = useFormField({
    defaultValue: "",
    requiredError: "toc_form.reason.error.required",
    validate: (value) => validateReason(value, isReasonRequired),
    dependsOn: [confidential.value],
  });

  const isTechnicianRequested = useFormSwitch({
    defaultValue: false,
  });

  const serviceTechnician = useFormApiAutoCompleteDropdown({
    defaultValue: serviceTechnicianDefault,
    fetchData: fetchPeople,
    validate: (value) =>
      validateServiceTechnician(value, isTechnicianRequested.value),
  });

  const plannedDate = useFormDatePicker({
    defaultValue: plannedDateDefault,
  });

  const formValidator = useFormValidator([
    equipmentRecordSerialNo,
    airport,
    serviceOrganisation,
    assignee,
    technician,
    ifactor,
    payer,
    serviceActivity,
    unitOperationalStatus,
    originalTitle,
    originalDescription,
    serviceTechnician,
    plannedDate,
    mainContact,
    hourMeter,
    reason,
    thirdPartyName,
  ]);

  const handleSubmit = () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      onSubmit({
        equipmentRecordSerialNo: equipmentRecordSerialNo.value || null,
        airport: airport.value,
        serviceOrganisation: serviceOrganisation.value,
        assignee: assignee.value,
        technician: technician.value,
        ifactor: ifactor.value,
        errorCodes: errorCodes.value,
        payer: payer.value,
        serviceActivity: serviceActivity.value,
        unitOperationalStatus: unitOperationalStatus.value,
        tags: tags.value,
        originalTitle: originalTitle.value,
        originalDescription: originalDescription.value,
        isTechnicianRequested: isTechnicianRequested.value,
        serviceTechnician: serviceTechnician.value,
        plannedDate: plannedDate.formattedValue,
        mainContact: mainContact.value,
        contacts: contacts.value,
        hourMeter: +(hourMeter.value || hourMeterOldValue),
        customer: customer.value,
        thirdPartyName: thirdPartyName.value,
        thirdPartyRef: thirdPartyRef.value,
        serialNumber: serialNumber.value,
        confidential: confidential.value,
        reason: reason.value,
      });
    }
  };

  useEffect(() => {
    if (
      !isEdit &&
      equipmentRecordSerialNo.value?.additionalInfo?.type === "EquipmentRecord"
    ) {
      const data = equipmentRecordSerialNo.value?.additionalInfo?.data;
      const newAirport = data?.airport;
      const newSso = data?.salesOrganisationService;
      const newCustomer = data?.endUser;
      const newSerialNumber = data?.customerSerialNumber || "";
      airport.clear();
      serviceOrganisation.clear();
      customer.clear();
      serialNumber.clear();
      contacts.clear();
      if (newAirport) {
        airport.helper.setValue(createAirportDropdownItem(newAirport));
      }
      if (newSso) {
        serviceOrganisation.helper.setValue(
          createServiceOrganisationDropdownItem(newSso)
        );
      }
      if (newCustomer) {
        customer.helper.setValue(createCustomerDropdownItem(newCustomer));
      }
      if (newSerialNumber) {
        serialNumber.helper.setValue(newSerialNumber);
      }
    }
    dispatch(
      fetchErContactOptions({
        equipmentRecord:
          equipmentRecordSerialNo.value?.additionalInfo?.data["@id"],
      })
    );
  }, [equipmentRecordSerialNo.value?.id]);

  useEffect(() => {
    setSelectedContacts(contacts.value);
  }, [contacts.value]);

  useEffect(() => {
    dispatch(
      fetchContactOptions({
        customer: customer.value?.id,
      })
    );
    mainContact.clear();
    if (customer.value?.id === customerDefault?.id) {
      mainContact.helper.setValue(mainContactDefault);
    }
  }, [customer.value?.id]);

  useEffect(() => {
    reason.clear();
  }, [confidential.value]);

  useEffect(() => {
    switch (serviceActivity.value?.id) {
      case TOC_SERVICE_ACTIVITY.COMMISSIONING: {
        confidential.helper.setValue(true);
        setTimeout(() => {
          reason.helper.setValue(t("toc_form.reason.value.default"));
        }, 0);
        break;
      }
      case TOC_SERVICE_ACTIVITY.INFO_REQUEST: {
        ifactor.helper.setValue(TOC_FILTER_IFACTOR_OPTIONS[0]);
        unitOperationalStatus.helper.setValue(TOC_UNIT_OPERATIONAL_STATUS_MCF);
        break;
      }
      default: {
        // empty on purpose
      }
    }
  }, [serviceActivity.value?.id]);

  useEffect(() => {
    contacts.clear();
    const defaultContacts: Array<IDropdownItem> = [];
    contacts.fieldProps.list.forEach((contact) => {
      if (contact.additionalInfo?.type === "ExtranetUser") {
        let isDefaultContact = false;
        contact.additionalInfo.data.extranetUserAcls?.forEach((acl) => {
          if (acl.extranetUserGroup.name === "fl_NOT_TOC") {
            isDefaultContact = true;
          }
        });
        if (isDefaultContact) {
          defaultContacts.push(contact);
        }
      }
    });
    contacts.helper.setValue(defaultContacts);
  }, [JSON.stringify(contacts.fieldProps.list)]);

  return (
    <div className="toc_form__wrapper">
      <ContactPopUp
        customer={customer.value?.id ?? ""}
        isOpen={isContactPopUpVisible}
        onClose={() => setIsContactPopUpVisible(false)}
      />
      <FormApiAutoCompleteDropdown
        {...equipmentRecordSerialNo.fieldProps}
        label="toc_form.er_sn.title"
        id="er-sn"
        placeholder="toc_form.er_sn.placeholder"
        dataCy="toc-form-equipment-record"
        disabled={isEdit}
      />
      <FormField
        {...serialNumber.fieldProps}
        label="toc_form.serial_number.title"
        id="serial-number"
        placeholder="toc_form.serial_number.placeholder"
        dataCy="toc-form-serial-number"
      />
      <FormApiAutoCompleteDropdown
        {...airport.fieldProps}
        label="toc_form.airport.title"
        requiredLabel
        id="airport"
        placeholder="toc_form.airport.placeholder"
        dataCy="toc-form-airport"
      />
      <FormApiAutoCompleteDropdown
        {...serviceOrganisation.fieldProps}
        label="toc_form.service_organisation.title"
        requiredLabel
        placeholder="toc_form.service_organisation.placeholder"
        dataCy="toc-form-service-organization"
      />
      <FormApiAutoCompleteDropdown
        {...customer.fieldProps}
        label="toc_form.customer.title"
        requiredLabel
        id="customer"
        placeholder="toc_form.customer.placeholder"
        dataCy="toc-form-customer"
      />
      <FormSingleSelectDropdown
        {...mainContact.fieldProps}
        label="toc_form.main_contact.title"
        placeholder={t("toc_form.main_contact.placeholder")}
        dataCy="toc-form-main-contact"
        requiredLabel
        labelElement={
          <Button
            className="toc_form__new_contact"
            variant="outlined"
            endIcon={<AddIcon />}
            size="small"
            onClick={() => setIsContactPopUpVisible(true)}
            disabled={!customer.value}
            data-cy="toc-new-contact-button"
          >
            <Typography variant="body2">{t("toc_form.new")}</Typography>
          </Button>
        }
      />
      <FormMutliSelectDropdown
        {...contacts.fieldProps}
        label="toc_form.contacts.title"
        placeholder={t("toc_form.contacts.placeholder")}
        dataCy="toc-form-contacts"
      />

      <FormApiAutoCompleteDropdown
        {...assignee.fieldProps}
        label="toc_form.assignee.title"
        requiredLabel
        id="assignee"
        placeholder="toc_form.assignee.placeholder"
        dataCy="toc-form-assignee"
      />
      <FormApiAutoCompleteDropdown
        {...technician.fieldProps}
        label="toc_form.new_technician.title"
        id="technician"
        placeholder="toc_form.new_technician.placeholder"
        dataCy="toc-form-technician"
      />
      <FormSingleSelectDropdown
        {...ifactor.fieldProps}
        label="toc_form.ifactor.title"
        requiredLabel
        placeholder="toc_form.ifactor.placeholder"
        dataCy="toc-form-ifactor"
      />
      <FormField
        {...errorCodes.fieldProps}
        label="toc_form.error_codes.title"
        id="error-codes"
        placeholder="toc_form.error_codes.placeholder"
        dataCy="toc-form-error-codes"
      />
      <FormSingleSelectDropdown
        {...payer.fieldProps}
        label="toc_form.payer.title"
        requiredLabel
        placeholder="toc_form.payer.placeholder"
        dataCy="toc-form-payer"
      />
      <FormSingleSelectDropdown
        {...serviceActivity.fieldProps}
        label="toc_form.service_activity.title"
        requiredLabel
        placeholder="toc_form.service_activity.placeholder"
        dataCy="toc-form-service-activity"
      />
      <FormSingleSelectDropdown
        {...unitOperationalStatus.fieldProps}
        label="toc_form.unit_operational_status.title"
        requiredLabel
        placeholder="toc_form.unit_operational_status.placeholder"
        dataCy="toc-form-unit-operational-status"
      />
      <FormMutliSelectDropdown
        {...tags.fieldProps}
        label="toc_form.tags.title"
        placeholder="toc_form.tags.placeholder"
        dataCy="toc-form-tags"
      />
      <FormField
        {...hourMeter.fieldProps}
        label="toc_form.hour_meter.title"
        subLabel={`(${t(
          "toc_form.hour_meter.sub_title"
        )} : ${hourMeterOldValue})`}
        id="toc-form-hour-meter"
        placeholder="toc_form.hour_meter.placeholder"
        dataCy="toc-form-hour-meter"
      />
      <FormField
        {...thirdPartyName.fieldProps}
        requiredLabel={!!thirdPartyRef.value}
        label="toc_form.third_party_name.title"
        id="third-party-name"
        placeholder="toc_form.third_party_name.placeholder"
        dataCy="toc-form-third-party-name"
      />
      <FormField
        {...thirdPartyRef.fieldProps}
        label="toc_form.third_party_ref.title"
        id="third-party-ref"
        placeholder="toc_form.third_party_ref.placeholder"
        dataCy="toc-form-third-party-ref"
      />
      <FormField
        {...originalTitle.fieldProps}
        label="toc_form.title.title"
        requiredLabel
        id="title"
        placeholder="toc_form.title.placeholder"
        dataCy="toc-form-title"
      />
      <FormRichTextField
        label="toc_form.description.title"
        requiredLabel
        {...originalDescription.fieldProps}
        placeholder="toc_form.description.placeholder"
        dataCy="toc-form-description"
      />
      <FormSwitch
        {...confidential.fieldProps}
        label="toc_form.confidential.title"
        className="cui_input_wrapper"
        dataCy="toc-form-confidential"
      />
      {isReasonRequired && (
        <FormField
          label="toc_form.reason.title"
          requiredLabel
          {...reason.fieldProps}
          placeholder="toc_form.reason.placeholder"
          dataCy="toc-form-reason"
          rows={4}
        />
      )}
      {!isEdit && equipmentRecordSerialNo.value && (
        <>
          <FeatureComponent
            features={["FEATURE_CUSTOMER_SERVICE_RECORD_CREATE"]}
          >
            <FormSwitch
              {...isTechnicianRequested.fieldProps}
              label="toc_form.technician.title"
              className="cui_input_wrapper"
              dataCy="toc-form-switch"
              labelTooltip="toc_form.technician.title_helper"
            />
          </FeatureComponent>

          {isTechnicianRequested.value && (
            <>
              <FormApiAutoCompleteDropdown
                {...serviceTechnician.fieldProps}
                label="toc_form.service_technician.title"
                requiredLabel
                id="service-technician"
                placeholder="toc_form.service_technician.placeholder"
                dataCy="toc-form-service-technician"
              />
              <FormDatePicker
                {...plannedDate.fieldProps}
                label="toc_form.planned_date.title"
                dataCy="toc-form-planned-date"
              />
            </>
          )}
        </>
      )}
      <Button
        disabled={formValidator.isSubmitDisabled}
        className="cui_button toc_form__submit"
        type="submit"
        variant="contained"
        onClick={handleSubmit}
        data-cy="toc-form-submit"
      >
        {t("toc_form.submit")}
      </Button>
    </div>
  );
}
