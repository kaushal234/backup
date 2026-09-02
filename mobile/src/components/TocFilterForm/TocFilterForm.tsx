import React from "react";
import "./TocFilterForm.css";
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
  BOOLEAN_OPTIONS,
  DATE_FORMAT,
  DATE_TODAY,
  TOC_FILTER_IFACTOR_OPTIONS,
} from "../../constants/constants";
import FormSingleSelectDropdown from "../FormSingleSelectDropdown/FormSingleSelectDropdown";
import { useFormSingleSelectDropdown } from "../../hooks/useFormSingleSelectDropdown";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import {
  initialState as tocFiltersInitialState,
  setTocFilters,
} from "../../redux/slices/tocFilterSlice";
import { ROUTES } from "../../constants/routes";
import { fetchEquipmentRecordSerialNo } from "../../utils/dropdown/equipmentRecordSerialNumber";
import { fetchAirport } from "../../utils/dropdown/airport";
import {
  createServiceOrganisationDropdownItem,
  fetchServiceOrganisationOptions,
} from "../../utils/dropdown/serviceOrganisation";
import { fetchEquipmentTypeOptions } from "../../utils/dropdown/equipmentType";
import { fetchManufacturerLocationOptions } from "../../utils/dropdown/manufacturerLocation";
import { fetchModelOptions } from "../../utils/dropdown/model";
import { fetchPeopleSearch } from "../../utils/dropdown/peopleSearch";
import { fetchSalesOrganisationOptions } from "../../utils/dropdown/salesOrganisation";
import { fetchServiceActivityOptions } from "../../utils/dropdown/serviceActivity";
import { fetchTechnicianOnCallTagOptions } from "../../utils/dropdown/technicianOnCallTag";
import { fetchTechnicianOnCallTypeOptions } from "../../utils/dropdown/technicianOnCallType";
import { fetchTocStatusOptions } from "../../utils/dropdown/tocStatus";
import { fetchUnitOperationalStatusOptions } from "../../utils/dropdown/unitOperationalStatus";
import { useFormValidator } from "../../hooks/useFormValidator";
import { useFormMultiSelectApiAutoCompleteDropdown } from "../../hooks/useFormMultiSelectApiAutoCompleteDropdown";
import FormMultiSelectApiAutoCompleteDropdown from "../FormMultiSelectApiAutoCompleteDropdown/FormMultiSelectApiAutoCompleteDropdown";
import { fetchCustomer } from "../../utils/dropdown/customer";
import { fetchCountry } from "../../utils/dropdown/country";
import { useFormField } from "../../hooks/useFormField";
import FormField from "../FormField/FormField";
import { getLocationById } from "../../api/getLocationById";
import { extractLocationId } from "../../utils/utils";
import { showMainLoader } from "../../redux/slices/loaderSlice";

const validateCreateAfter = (
  createAfterFormattedValue: string | null,
  createBeforeFormattedValue: string | null
) => {
  if (!createBeforeFormattedValue) return "";
  if (
    dayjs(createAfterFormattedValue).isAfter(dayjs(createBeforeFormattedValue))
  ) {
    return "toc_search.created_after.error.after_before";
  }
  return "";
};

const validateSolvedAfter = (
  solvedAfterFormattedValue: string | null,
  solvedBeforeFormattedValue: string | null
) => {
  if (!solvedBeforeFormattedValue) return "";
  if (
    dayjs(solvedAfterFormattedValue).isAfter(dayjs(solvedBeforeFormattedValue))
  ) {
    return "toc_search.solved_after.error.after_before";
  }
  return "";
};

function TocFilterForm() {
  const { t } = useTranslation();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const userInfo = useAppSelector((state) => state.auth.userInfo);
  const tocFilters = useAppSelector((state) => state.tocFilter.filters);

  const equipmentRecordSerialNo = useFormApiAutoCompleteDropdown({
    defaultValue: tocFilters.serialNumber,
    fetchData: fetchEquipmentRecordSerialNo,
  });

  const status = useFormMultiSelectDropdown({
    defaultValue: tocFilters.status,
    list: [],
    fetchOptions: fetchTocStatusOptions,
    selector: (state) => state.dropdownOption.tocStatus,
  });

  const assignee = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: tocFilters.assignee,
    fetchData: fetchPeopleSearch,
  });

  const unitOperationalStatus = useFormMultiSelectDropdown({
    defaultValue: tocFilters.unitOperationalStatus,
    list: [],
    fetchOptions: fetchUnitOperationalStatusOptions,
    selector: (state) => state.dropdownOption.unitOperationalStatus,
  });

  const payer = useFormMultiSelectDropdown({
    defaultValue: tocFilters.technicianOnCallType,
    list: [],
    fetchOptions: fetchTechnicianOnCallTypeOptions,
    selector: (state) => state.dropdownOption.technicianOnCallType,
  });

  const serviceActivity = useFormMultiSelectDropdown({
    defaultValue: tocFilters.serviceActivity,
    list: [],
    fetchOptions: fetchServiceActivityOptions,
    selector: (state) => state.dropdownOption.serviceActivity,
  });

  const ifactor = useFormMultiSelectDropdown({
    defaultValue: tocFilters.indiceFactor,
    list: TOC_FILTER_IFACTOR_OPTIONS,
  });

  const tags = useFormMultiSelectDropdown({
    defaultValue: tocFilters.tags,
    list: [],
    fetchOptions: fetchTechnicianOnCallTagOptions,
    selector: (state) => state.dropdownOption.tocTags,
  });

  const createdBy = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: tocFilters.createdBy,
    fetchData: fetchPeopleSearch,
  });

  const createdBefore = useFormDatePicker({
    defaultValue: tocFilters.createdBefore,
    maxDate: DATE_TODAY,
  });

  const createdAfter = useFormDatePicker({
    defaultValue: tocFilters.createdAfter,
    maxDate: DATE_TODAY,
    dependsOn: [createdBefore.value],
    validate: (value) =>
      validateCreateAfter(
        value?.format(DATE_FORMAT) ?? "",
        createdBefore.formattedValue
      ),
  });

  const solvedBefore = useFormDatePicker({
    defaultValue: tocFilters.solvedBefore,
    maxDate: DATE_TODAY,
  });

  const solvedAfter = useFormDatePicker({
    defaultValue: tocFilters.solvedAfter,
    maxDate: DATE_TODAY,
    dependsOn: [solvedBefore.value],
    validate: (value) =>
      validateSolvedAfter(
        value?.format(DATE_FORMAT) ?? "",
        solvedBefore.formattedValue
      ),
  });

  const salesOrganisation = useFormMultiSelectDropdown({
    defaultValue: tocFilters.salesOrganisation,
    list: [],
    fetchOptions: fetchSalesOrganisationOptions,
    selector: (state) => state.dropdownOption.salesOrganisation,
  });

  const serviceOrganisation = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: tocFilters.serviceOrganisation,
    fetchData: fetchServiceOrganisationOptions,
  });

  const manufacturerLocation = useFormMultiSelectDropdown({
    defaultValue: tocFilters.manufacturerLocation,
    list: [],
    fetchOptions: fetchManufacturerLocationOptions,
    selector: (state) => state.dropdownOption.manufacturerLocation,
  });

  const equipmentType = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: tocFilters.equipmentType,
    fetchData: fetchEquipmentTypeOptions,
  });

  const models = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: tocFilters.model,
    fetchData: fetchModelOptions,
  });

  const airport = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: tocFilters.airport,
    fetchData: fetchAirport,
  });

  const late = useFormSingleSelectDropdown({
    defaultValue: tocFilters.late,
    list: BOOLEAN_OPTIONS,
  });

  const factoryFlag = useFormSingleSelectDropdown({
    defaultValue: tocFilters.factoryFlag,
    list: BOOLEAN_OPTIONS,
  });

  const factoryFlagRecentlyClosed = useFormSingleSelectDropdown({
    defaultValue: tocFilters.factoryFlagRecentlyClosed,
    list: BOOLEAN_OPTIONS,
  });

  const survey = useFormSingleSelectDropdown({
    defaultValue: tocFilters.survey,
    list: BOOLEAN_OPTIONS,
  });

  const buyer = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: tocFilters.buyer,
    fetchData: fetchCustomer,
  });

  const country = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: tocFilters.country,
    fetchData: fetchCountry,
  });

  const tocPart = useFormField({
    defaultValue: tocFilters.tocPart,
  });

  const endUser = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: tocFilters.endUser,
    fetchData: fetchCustomer,
  });

  const sprPart = useFormField({
    defaultValue: tocFilters.sprPart,
  });

  const maintainer = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: tocFilters.maintainer,
    fetchData: fetchCustomer,
  });

  const confidential = useFormSingleSelectDropdown({
    defaultValue: tocFilters.confidential,
    list: BOOLEAN_OPTIONS,
  });

  const title = useFormField({
    defaultValue: tocFilters.title,
  });

  const technician = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: tocFilters.technician,
    fetchData: fetchPeopleSearch,
  });

  const errorCodes = useFormField({
    defaultValue: tocFilters.errorCodes,
  });

  const assigneeOrTechnician = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: tocFilters.assigneeOrTechnician,
    fetchData: fetchPeopleSearch,
  });

  const formValidator = useFormValidator([createdAfter, solvedAfter]);

  const handleValidate = () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      dispatch(
        setTocFilters({
          serialNumber: equipmentRecordSerialNo.value,
          status: status.value,
          assignee: assignee.value,
          unitOperationalStatus: unitOperationalStatus.value,
          technicianOnCallType: payer.value,
          serviceActivity: serviceActivity.value,
          indiceFactor: ifactor.value,
          tags: tags.value,
          createdBy: createdBy.value,
          createdAfter: createdAfter.formattedValue,
          createdBefore: createdBefore.formattedValue,
          solvedAfter: solvedAfter.formattedValue,
          solvedBefore: solvedBefore.formattedValue,
          salesOrganisation: salesOrganisation.value,
          serviceOrganisation: serviceOrganisation.value,
          manufacturerLocation: manufacturerLocation.value,
          equipmentType: equipmentType.value,
          model: models.value,
          airport: airport.value,
          late: late.value,
          factoryFlag: factoryFlag.value,
          factoryFlagRecentlyClosed: factoryFlagRecentlyClosed.value,
          survey: survey.value,
          buyer: buyer.value,
          country: country.value,
          tocPart: tocPart.value,
          endUser: endUser.value,
          sprPart: sprPart.value,
          maintainer: maintainer.value,
          confidential: confidential.value,
          title: title.value,
          technician: technician.value,
          errorCodes: errorCodes.value,
          assigneeOrTechnician: assigneeOrTechnician.value,
        })
      );
      navigate(ROUTES.toc.home);
    }
  };

  const handleClearFilters = async () => {
    dispatch(showMainLoader(true));
    const match = await getLocationById({
      id: extractLocationId(userInfo?.businessUnit?.location["@id"]),
    });
    dispatch(showMainLoader(false));
    dispatch(
      setTocFilters({
        ...tocFiltersInitialState.filters,
        serviceOrganisation: match.data
          ? [createServiceOrganisationDropdownItem(match.data)]
          : tocFiltersInitialState.filters.serviceOrganisation,
      })
    );
    navigate(ROUTES.toc.home);
  };

  return (
    <Paper className="toc_filter__filters_wrapper" elevation={1}>
      <div className="toc_filter__heading_wrapper">
        <Typography
          className="cui_light_text"
          variant="h5"
          gutterBottom
          data-cy="toc-filter-heading"
        >
          {t("toc.filter.heading")}
        </Typography>
        <Typography variant="body2">
          <Link href="#" onClick={handleClearFilters}>
            {t("common.clear_filters")}
          </Link>
        </Typography>
      </div>
      <div className="toc_filter__fields_wrapper">
        <FormMultiSelectApiAutoCompleteDropdown
          {...serviceOrganisation.fieldProps}
          label="toc_search.service_organisation.title"
          placeholder="toc_search.service_organisation.placeholder"
          dataCy="toc-filter-service-organisation"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...assignee.fieldProps}
          label="toc_search.assignee.title"
          id="assignee"
          placeholder="toc_search.assignee.placeholder"
          dataCy="toc-filter-assignee"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...technician.fieldProps}
          label="toc_search.technician.title"
          id="technician"
          placeholder="toc_search.technician.placeholder"
          dataCy="toc-filter-technician"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...assigneeOrTechnician.fieldProps}
          label="toc_search.assignee_or_technician.title"
          id="assignee-or-technician"
          placeholder="toc_search.assignee_or_technician.placeholder"
          dataCy="toc-filter-assignee-or-technician"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...airport.fieldProps}
          label="toc_search.airport.title"
          id="airport"
          placeholder="toc_search.airport.placeholder"
          dataCy="toc-filter-airport"
        />
        <FormMutliSelectDropdown
          {...ifactor.fieldProps}
          label="toc_search.ifactor.title"
          placeholder="toc_search.ifactor.placeholder"
          dataCy="toc-filter-ifactor"
        />
        <FormMutliSelectDropdown
          {...unitOperationalStatus.fieldProps}
          label="toc_search.unit_operational_status.title"
          placeholder="toc_search.unit_operational_status.placeholder"
          dataCy="toc-filter-unit-operational-status"
        />
        <FormMutliSelectDropdown
          {...serviceActivity.fieldProps}
          label="toc_search.service_activity.title"
          placeholder="toc_search.service_activity.placeholder"
          dataCy="toc-filter-service-activity"
        />
        <FormMutliSelectDropdown
          {...status.fieldProps}
          label="toc_search.status.title"
          placeholder="toc_search.status.placeholder"
          dataCy="toc-filter-status"
        />
        <FormApiAutoCompleteDropdown
          {...equipmentRecordSerialNo.fieldProps}
          label="toc_search.er_sn.title"
          id="er-sn"
          triggerSearchAtChar={1}
          placeholder="toc_search.er_sn.placeholder"
          optionsPlaceholder="toc_search.er_sn.options_placeholder"
          dataCy="toc-filter-serial-number"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...equipmentType.fieldProps}
          label="toc_search.equipment_type.title"
          placeholder="toc_search.equipment_type.placeholder"
          dataCy="toc-filter-equipment-type"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...models.fieldProps}
          label="toc_search.models.title"
          placeholder="toc_search.models.placeholder"
          dataCy="toc-filter-models"
        />
        <FormMutliSelectDropdown
          {...payer.fieldProps}
          label="toc_search.payer.title"
          placeholder="toc_search.payer.placeholder"
          dataCy="toc-filter-payer"
        />
        <FormMutliSelectDropdown
          {...tags.fieldProps}
          label="toc_search.tags.title"
          placeholder="toc_search.tags.placeholder"
          dataCy="toc-filter-tags"
        />
        <FormMutliSelectDropdown
          {...salesOrganisation.fieldProps}
          label="toc_search.sales_organisation.title"
          placeholder="toc_search.sales_organisation.placeholder"
          dataCy="toc-filter-sales-organisation"
        />
        <FormMutliSelectDropdown
          {...manufacturerLocation.fieldProps}
          label="toc_search.manufacturer_location.title"
          placeholder="toc_search.manufacturer_location.placeholder"
          dataCy="toc-filter-manufacturer-location"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...createdBy.fieldProps}
          label="toc_search.created_by.title"
          id="created-by"
          placeholder="toc_search.created_by.placeholder"
          dataCy="toc-filter-created-by"
        />
        <FormDatePicker
          {...createdAfter.fieldProps}
          label="toc_search.created_after.title"
          dataCy="toc-filter-created-after"
        />
        <FormDatePicker
          {...createdBefore.fieldProps}
          label="toc_search.created_before.title"
          dataCy="toc-filter-created-before"
        />
        <FormDatePicker
          {...solvedAfter.fieldProps}
          label="toc_search.solved_after.title"
          dataCy="toc-filter-solved-after"
        />
        <FormDatePicker
          {...solvedBefore.fieldProps}
          label="toc_search.solved_before.title"
          dataCy="toc-filter-solved-before"
        />
        <FormSingleSelectDropdown
          {...late.fieldProps}
          label="toc_search.late.title"
          placeholder="toc_search.late.placeholder"
          dataCy="toc-filter-late"
        />
        <FormSingleSelectDropdown
          {...factoryFlag.fieldProps}
          label="toc_search.factory_flag.title"
          placeholder="toc_search.factory_flag.placeholder"
          dataCy="toc-filter-factory-flag"
        />
        <FormSingleSelectDropdown
          {...factoryFlagRecentlyClosed.fieldProps}
          label="toc_search.factory_flag_recently_closed.title"
          placeholder="toc_search.factory_flag_recently_closed.placeholder"
          dataCy="toc-filter-factory-flag-recently-closed"
        />
        <FormSingleSelectDropdown
          {...survey.fieldProps}
          label="toc_search.survey.title"
          placeholder="toc_search.survey.placeholder"
          dataCy="toc-filter-survey"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...country.fieldProps}
          label="toc_search.country.title"
          id="country"
          triggerSearchAtChar={1}
          placeholder="toc_search.country.placeholder"
          dataCy="toc-filter-country"
        />
        <FormField
          {...tocPart.fieldProps}
          label="toc_search.toc_part.title"
          placeholder="toc_search.toc_part.title"
          dataCy="toc-part"
        />
        <FormField
          {...sprPart.fieldProps}
          label="toc_search.spr_part.title"
          placeholder="toc_search.spr_part.title"
          dataCy="spr-part"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...endUser.fieldProps}
          label="toc_search.end_user.title"
          id="end-user"
          triggerSearchAtChar={1}
          placeholder="toc_search.end_user.placeholder"
          dataCy="toc-filter-end-user"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...buyer.fieldProps}
          label="toc_search.buyer.title"
          id="buyer"
          triggerSearchAtChar={1}
          placeholder="toc_search.buyer.placeholder"
          dataCy="toc-filter-buyer"
        />
        <FormMultiSelectApiAutoCompleteDropdown
          {...maintainer.fieldProps}
          label="toc_search.maintainer.title"
          id="maintainer"
          triggerSearchAtChar={1}
          placeholder="toc_search.maintainer.placeholder"
          dataCy="toc-filter-maintainer"
        />
        <FormSingleSelectDropdown
          {...confidential.fieldProps}
          label="toc_search.confidential.title"
          placeholder="toc_search.confidential.placeholder"
          dataCy="toc-filter-confidential"
        />
        <FormField
          {...title.fieldProps}
          label="toc_search.title.title"
          placeholder="toc_search.title.title"
          dataCy="toc-title"
        />
        <FormField
          {...errorCodes.fieldProps}
          label="toc_search.error_codes.title"
          placeholder="toc_search.error_codes.placeholder"
          dataCy="toc-error-codes"
        />
        <Button
          disabled={formValidator.isSubmitDisabled}
          className="cui_button toc_filter_form__validate_button"
          type="submit"
          variant="contained"
          onClick={handleValidate}
          data-cy="toc-filter-submit"
        >
          {t("toc_search.validate")}
        </Button>
      </div>
    </Paper>
  );
}

export default TocFilterForm;
