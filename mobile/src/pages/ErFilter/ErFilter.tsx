import React from "react";
import "./ErFilter.css";
import { useNavigate } from "react-router";
import { Paper, Button, Typography, Link } from "@mui/material";
import { useTranslation } from "react-i18next";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import FormApiAutoCompleteDropdown from "../../components/FormApiAutoCompleteDropdown/FormApiAutoCompleteDropdown";
import { useFormApiAutoCompleteDropdown } from "../../hooks/useFormApiAutoCompleteDropdown";
import {
  initialState as erFiltersInitialState,
  setErFilters,
} from "../../redux/slices/erFilterSlice";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllEquipmentRecord } from "../../api/getAllEquipmentRecord";
import { fetchAirport } from "../../utils/dropdown/airport";
import { fetchEquipmentTypeOptions } from "../../utils/dropdown/equipmentType";
import { fetchModelOptions } from "../../utils/dropdown/model";
import { fetchCustomer } from "../../utils/dropdown/customer";
import { useFormMultiSelectApiAutoCompleteDropdown } from "../../hooks/useFormMultiSelectApiAutoCompleteDropdown";
import FormMultiSelectApiAutoCompleteDropdown from "../../components/FormMultiSelectApiAutoCompleteDropdown/FormMultiSelectApiAutoCompleteDropdown";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.er.home", link: ROUTES.er.home },
  { title: "breadcrumb.er.filters", link: "" },
];

export const fetchEquipmentRecordSerialNo = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllEquipmentRecord({ serialNumber: value });
  return (response.data?.["hydra:member"] ?? []).map((item) => ({
    id: item.serialNumber,
    text: item.serialNumber,
  }));
};

function ErFilter() {
  useDrawer(ROUTES.toc.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const erFilters = useAppSelector((state) => state.erFilter.filters);

  const equipmentRecordSerialNo = useFormApiAutoCompleteDropdown({
    defaultValue: erFilters.serialNumber,
    fetchData: fetchEquipmentRecordSerialNo,
  });

  const equipmentType = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: erFilters.equipmentType,
    fetchData: fetchEquipmentTypeOptions,
  });

  const models = useFormMultiSelectApiAutoCompleteDropdown({
    defaultValue: erFilters.model,
    fetchData: fetchModelOptions,
  });

  const airport = useFormApiAutoCompleteDropdown({
    defaultValue: erFilters.airport,
    fetchData: fetchAirport,
  });

  const buyer = useFormApiAutoCompleteDropdown({
    defaultValue: erFilters.buyer,
    fetchData: fetchCustomer,
  });

  const endUser = useFormApiAutoCompleteDropdown({
    defaultValue: erFilters.endUser,
    fetchData: fetchCustomer,
  });

  const maintainer = useFormApiAutoCompleteDropdown({
    defaultValue: erFilters.maintainer,
    fetchData: fetchCustomer,
  });

  const handleValidate = () => {
    dispatch(
      setErFilters({
        serialNumber: equipmentRecordSerialNo.value,
        equipmentType: equipmentType.value,
        model: models.value,
        airport: airport.value,
        buyer: buyer.value,
        endUser: endUser.value,
        maintainer: maintainer.value,
      })
    );
    navigate(ROUTES.er.home);
  };

  const handleClearFilters = () => {
    dispatch(setErFilters(erFiltersInitialState.filters));
    navigate(ROUTES.er.home);
  };

  return (
    <div className="er_filter__wrapper">
      <Paper className="er_filter__filters_wrapper" elevation={1}>
        <div className="er_filter__heading_wrapper">
          <Typography
            className="cui_light_text"
            variant="h5"
            gutterBottom
            data-cy="er-filter-heading"
          >
            {t("toc.filter.heading")}
          </Typography>
          <Typography variant="body2">
            <Link href="#" onClick={handleClearFilters}>
              {t("common.clear_filters")}
            </Link>
          </Typography>
        </div>
        <div className="er_filter__fields_wrapper">
          <FormApiAutoCompleteDropdown
            {...equipmentRecordSerialNo.fieldProps}
            label="er_filter.er_sn.title"
            id="er-sn"
            triggerSearchAtChar={1}
            placeholder="er_filter.er_sn.placeholder"
            optionsPlaceholder="er_filter.er_sn.options_placeholder"
            dataCy="er-filter-serial-number"
          />
          <FormApiAutoCompleteDropdown
            {...airport.fieldProps}
            label="er_filter.airport.title"
            id="airport"
            placeholder="er_filter.airport.placeholder"
            dataCy="er-filter-airport"
          />
          <FormMultiSelectApiAutoCompleteDropdown
            {...equipmentType.fieldProps}
            label="er_filter.equipment_type.title"
            placeholder="er_filter.equipment_type.placeholder"
            dataCy="er-filter-equipment-type"
          />
          <FormMultiSelectApiAutoCompleteDropdown
            {...models.fieldProps}
            label="er_filter.models.title"
            placeholder="er_filter.models.placeholder"
            dataCy="er-filter-models"
          />
          <FormApiAutoCompleteDropdown
            {...buyer.fieldProps}
            label="er_filter.buyer.title"
            id="buyer"
            placeholder="er_filter.buyer.placeholder"
            dataCy="er-filter-buyer"
          />
          <FormApiAutoCompleteDropdown
            {...endUser.fieldProps}
            label="er_filter.end_user.title"
            id="end-user"
            placeholder="er_filter.end_user.placeholder"
            dataCy="er-filter-end-user"
          />
          <FormApiAutoCompleteDropdown
            {...maintainer.fieldProps}
            label="er_filter.maintainer.title"
            id="maintainer"
            placeholder="er_filter.maintainer.placeholder"
            dataCy="er-filter-maintainer"
          />
          <Button
            className="cui_button toc_filter_form__validate_button"
            type="submit"
            variant="contained"
            onClick={handleValidate}
            data-cy="er-filter-submit"
          >
            {t("er_filter.validate")}
          </Button>
        </div>
      </Paper>
    </div>
  );
}

export default ErFilter;
