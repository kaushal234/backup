import React from "react";
import "./CsrFilterForm.css";
import { Button, Link, Paper, Typography } from "@mui/material";
import { useTranslation } from "react-i18next";
import dayjs from "dayjs";
import { useNavigate } from "react-router";
import FormApiAutoCompleteDropdown from "../FormApiAutoCompleteDropdown/FormApiAutoCompleteDropdown";
import { useFormApiAutoCompleteDropdown } from "../../hooks/useFormApiAutoCompleteDropdown";
import FormMutliSelectDropdown from "../FormMutliSelectDropdown/FormMutliSelectDropdown";
import { useFormMultiSelectDropdown } from "../../hooks/useFormMultiSelectDropdown";
import FormDatePicker from "../FormDatePicker/FormDatePicker";
import { useFormDatePicker } from "../../hooks/useFormDatePicker";
import {
  CSR_FILTER_DISCRIMINATOR,
  DATE_FORMAT,
  DATE_TODAY,
} from "../../constants/constants";
import FormSingleSelectDropdown from "../FormSingleSelectDropdown/FormSingleSelectDropdown";
import { useFormSingleSelectDropdown } from "../../hooks/useFormSingleSelectDropdown";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { ROUTES } from "../../constants/routes";
import { fetchEquipmentRecordSerialNo } from "../../utils/dropdown/equipmentRecordSerialNumber";
import { fetchAirport } from "../../utils/dropdown/airport";
import { fetchServiceOrganisationOptions } from "../../utils/dropdown/serviceOrganisation";
import { fetchCountryOptions } from "../../utils/dropdown/country";
import { fetchCsrStatusOptions } from "../../utils/dropdown/csrStatus";
import { fetchEquipmentTypeOptions } from "../../utils/dropdown/equipmentType";
import { fetchManufacturerLocationOptions } from "../../utils/dropdown/manufacturerLocation";
import { fetchModelOptions } from "../../utils/dropdown/model";
import { fetchPeopleSearch } from "../../utils/dropdown/peopleSearch";
import { fetchSalesOrganisationOptions } from "../../utils/dropdown/salesOrganisation";
import {
  initialState as csrFiltersInitialState,
  setCsrFilters,
} from "../../redux/slices/csrFilterSlice";
import { useFormValidator } from "../../hooks/useFormValidator";
import { fetchCustomer } from "../../utils/dropdown/customer";
import { useFormMultiSelectApiAutoCompleteDropdown } from "../../hooks/useFormMultiSelectApiAutoCompleteDropdown";
import FormMultiSelectApiAutoCompleteDropdown from "../FormMultiSelectApiAutoCompleteDropdown/FormMultiSelectApiAutoCompleteDropdown";

const validateAfterDate = (data: {
  afterFormattedValue: string | null;
  beforeFormattedValue: string | null;
  error: string;
}) => {
  if (!data.beforeFormattedValue) return "";
  if (
    dayjs(data.afterFormattedValue).isAfter(dayjs(data.beforeFormattedValue))
  ) {
    return data.error;
  }
  return "";
};

function CsrFilterForm() {
  const { t } = useTranslation();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const csrFilters = useAppSelector((state) => state.csrFilter.filters);

  const equipmentRecordSerialNo = useFormApiAutoCompleteDropdown({
    defaultValue: csrFilters.serialNumber,
    fetchData: fetchEquipmentRecordSerialNo,
  });

  const status = useFormMultiSelectDropdown({
    defaultValue: csrFilters.status,
    list: [],
    fetchOptions: fetchCsrStatusOptions,
    selector: (state) => state.dropdownOption.csrStatus,
  });

  const createdBy = useFormApiAutoCompleteDropdown({
    defaultValue: csrFilters.createdBy,
    fetchData: fetchPeopleSearch,
  });

  const createdBefore = useFormDatePicker({
    defaultValue: csrFilters.createdBefore,
    maxDate: DATE_TODAY,
  });

  const createdAfter = useFormDatePicker({
    defaultValue: csrFilters.createdAfter,
    maxDate: DATE_TODAY,
    validate: (value) =>
      validateAfterDate({
        afterFormattedValue: value?.format(DATE_FORMAT) ?? "",
        beforeFormattedValue: createdBefore.formattedValue,
        error: "csr_filter.created_after.error.after_before",
      }),
    dependsOn: [createdBefore.value],
  });

  const salesOrganisation = useFormSingleSelectDropdown({
    defaultValue: csrFilters.salesOrganisation,
    list: [],
    fetchOptions: fetchSalesOrganisationOptions,
    selector: (state) => state.dropdownOption.salesOrganisation,
  });

  const serviceOrganisation = useFormApiAutoCompleteDropdown({
    defaultValue: csrFilters.serviceOrganisation,
    fetchData: fetchServiceOrganisationOptions,
  });

  const manufacturerLocation = useFormSingleSelectDropdown({
    defaultValue: csrFilters.manufacturerLocation,
    list: [],
    fetchOptions: fetchManufacturerLocationOptions,
    selector: (state) => state.dropdownOption.manufacturerLocation,
  });

  const equipmentType = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: csrFilters.equipmentType,
    fetchData: fetchEquipmentTypeOptions,
  });

  const models = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: csrFilters.model,
    fetchData: fetchModelOptions,
  });

  const airport = useFormApiAutoCompleteDropdown({
    defaultValue: csrFilters.airport,
    fetchData: fetchAirport,
  });

  const serviceTechnician = useFormApiAutoCompleteDropdown({
    defaultValue: csrFilters.serviceTechnician,
    fetchData: fetchPeopleSearch,
  });

  const endUser = useFormApiAutoCompleteDropdown({
    defaultValue: csrFilters.endUser,
    fetchData: fetchCustomer,
  });

  const completedBefore = useFormDatePicker({
    defaultValue: csrFilters.completedBefore,
    maxDate: DATE_TODAY,
  });

  const completedAfter = useFormDatePicker({
    defaultValue: csrFilters.completedAfter,
    maxDate: DATE_TODAY,
    validate: (value) =>
      validateAfterDate({
        afterFormattedValue: value?.format(DATE_FORMAT) ?? "",
        beforeFormattedValue: completedBefore.formattedValue,
        error: "csr_filter.completed_after.error.after_before",
      }),
    dependsOn: [completedBefore.value],
  });

  const closedBefore = useFormDatePicker({
    defaultValue: csrFilters.closedBefore,
    maxDate: DATE_TODAY,
  });

  const closedAfter = useFormDatePicker({
    defaultValue: csrFilters.closedAfter,
    maxDate: DATE_TODAY,
    validate: (value) =>
      validateAfterDate({
        afterFormattedValue: value?.format(DATE_FORMAT) ?? "",
        beforeFormattedValue: closedBefore.formattedValue,
        error: "csr_filter.closed_after.error.after_before",
      }),
    dependsOn: [closedBefore.value],
  });

  const country = useFormMultiSelectDropdown({
    defaultValue: csrFilters.country,
    list: [],
    fetchOptions: fetchCountryOptions,
    selector: (state) => state.dropdownOption.country,
  });

  const discriminator = useFormMultiSelectDropdown({
    defaultValue: csrFilters.discriminator,
    list: CSR_FILTER_DISCRIMINATOR,
  });

  const formValidator = useFormValidator([
    createdAfter,
    completedAfter,
    closedAfter,
  ]);

  const handleValidate = () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      dispatch(
        setCsrFilters({
          serialNumber: equipmentRecordSerialNo.value,
          status: status.value,
          createdBy: createdBy.value,
          createdAfter: createdAfter.formattedValue,
          createdBefore: createdBefore.formattedValue,
          salesOrganisation: salesOrganisation.value,
          serviceOrganisation: serviceOrganisation.value,
          manufacturerLocation: manufacturerLocation.value,
          equipmentType: equipmentType.value,
          model: models.value,
          airport: airport.value,
          serviceTechnician: serviceTechnician.value,
          endUser: endUser.value,
          completedAfter: completedAfter.formattedValue,
          completedBefore: completedBefore.formattedValue,
          closedAfter: closedAfter.formattedValue,
          closedBefore: closedBefore.formattedValue,
          country: country.value,
          discriminator: discriminator.value,
        })
      );
      navigate(ROUTES.csr.home);
    }
  };

  const handleClearFilters = () => {
    dispatch(setCsrFilters(csrFiltersInitialState.filters));
    navigate(ROUTES.csr.home);
  };

  return (
    <Paper className="csr_filter__filters_wrapper" elevation={1}>
      <div className="csr_filter__heading_wrapper">
        <Typography
          className="cui_light_text"
          variant="h5"
          gutterBottom
          data-cy="csr-filter-heading"
        >
          {t("toc.filter.heading")}
        </Typography>
        <Typography variant="body2">
          <Link href="#" onClick={handleClearFilters}>
            {t("common.clear_filters")}
          </Link>
        </Typography>
      </div>
      <div className="csr_filter__fields_wrapper">
        <FormApiAutoCompleteDropdown
          {...serviceOrganisation.fieldProps}
          label="csr_filter.service_organisation.title"
          placeholder="csr_filter.service_organisation.placeholder"
          dataCy="csr-filter-service-organisation"
        />
        <FormApiAutoCompleteDropdown
          {...airport.fieldProps}
          label="csr_filter.airport.title"
          id="airport"
          placeholder="csr_filter.airport.placeholder"
          dataCy="csr-filter-airport"
        />
        <FormMutliSelectDropdown
          {...status.fieldProps}
          label="csr_filter.status.title"
          placeholder="csr_filter.status.placeholder"
          dataCy="csr-filter-status"
        />
        <FormApiAutoCompleteDropdown
          {...equipmentRecordSerialNo.fieldProps}
          label="csr_filter.er_sn.title"
          id="er-sn"
          triggerSearchAtChar={1}
          placeholder="csr_filter.er_sn.placeholder"
          optionsPlaceholder="csr_filter.er_sn.options_placeholder"
          dataCy="csr-filter-serial-number"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...equipmentType.fieldProps}
          label="csr_filter.equipment_type.title"
          placeholder="csr_filter.equipment_type.placeholder"
          dataCy="csr-filter-equipment-type"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...models.fieldProps}
          label="csr_filter.models.title"
          placeholder="csr_filter.models.placeholder"
          dataCy="csr-filter-models"
        />
        <FormSingleSelectDropdown
          {...salesOrganisation.fieldProps}
          label="csr_filter.sales_organisation.title"
          placeholder="csr_filter.sales_organisation.placeholder"
          dataCy="csr-filter-sales-organisation"
        />
        <FormSingleSelectDropdown
          {...manufacturerLocation.fieldProps}
          label="csr_filter.manufacturer_location.title"
          placeholder="csr_filter.manufacturer_location.placeholder"
          dataCy="csr-filter-manufacturer-location"
        />
        <FormApiAutoCompleteDropdown
          {...createdBy.fieldProps}
          label="csr_filter.created_by.title"
          id="created-by"
          placeholder="csr_filter.created_by.placeholder"
          dataCy="csr-filter-created-by"
        />
        <FormDatePicker
          {...createdAfter.fieldProps}
          label="csr_filter.created_after.title"
          dataCy="csr-filter-created-after"
        />
        <FormDatePicker
          {...createdBefore.fieldProps}
          label="csr_filter.created_before.title"
          dataCy="csr-filter-created-before"
        />
        <FormApiAutoCompleteDropdown
          {...serviceTechnician.fieldProps}
          label="csr_filter.service_technician.title"
          id="service-technician"
          placeholder="csr_filter.service_technician.placeholder"
          dataCy="csr-filter-service-technician"
        />
        <FormApiAutoCompleteDropdown
          {...endUser.fieldProps}
          label="csr_filter.end_user.title"
          id="end-user"
          placeholder="csr_filter.end_user.placeholder"
          dataCy="csr-filter-end-user"
        />
        <FormDatePicker
          {...completedAfter.fieldProps}
          label="csr_filter.completed_after.title"
          dataCy="csr-filter-completed-after"
        />
        <FormDatePicker
          {...completedBefore.fieldProps}
          label="csr_filter.completed_before.title"
          dataCy="csr-filter-completed-before"
        />
        <FormDatePicker
          {...closedAfter.fieldProps}
          label="csr_filter.closed_after.title"
          dataCy="csr-filter-closed-after"
        />
        <FormDatePicker
          {...closedBefore.fieldProps}
          label="csr_filter.closed_before.title"
          dataCy="csr-filter-closed-before"
        />
        <FormMutliSelectDropdown
          {...country.fieldProps}
          label="csr_filter.country.title"
          placeholder="csr_filter.country.placeholder"
          dataCy="csr-filter-country"
        />
        <FormMutliSelectDropdown
          {...discriminator.fieldProps}
          label="csr_filter.discriminator.title"
          placeholder="csr_filter.discriminator.placeholder"
          dataCy="csr-filter-csr-type"
        />
        <Button
          disabled={formValidator.isSubmitDisabled}
          className="cui_button toc_filter_form__validate_button"
          type="submit"
          variant="contained"
          onClick={handleValidate}
          data-cy="csr-filter-submit"
        >
          {t("csr_filter.validate")}
        </Button>
      </div>
    </Paper>
  );
}

export default CsrFilterForm;
