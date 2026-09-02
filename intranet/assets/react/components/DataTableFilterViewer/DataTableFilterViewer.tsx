import React from "react";
import "./DataTableFilterViewer.css";
import CloseIcon from "@mui/icons-material/Close";
import { change } from "redux-form";
import { Box } from "@mui/material";
import {
  GENERIC_FILTER_FORM_NAME,
  IGenericFilterField,
} from "../GenericFilterForm/GenericFilterForm";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { dataTableActions } from "../../reducers/dataTable/dataTableSlice";
import {
  convertFormValueToFormattedValue,
  saveDataTableSettings,
} from "../../utils/utils";

interface IProps {
  filterFields: Array<IGenericFilterField>;
  name: string;
}

function DataTableFilterViewer(props: IProps) {
  const { filterFields, name } = props;
  const dispatch = useAppDispatch();

  const { filters, isLoading } = useAppSelector((state) => state.dataTable);

  const handleClearFilter = async (key: string) => {
    if (isLoading) return;
    dispatch(change(GENERIC_FILTER_FORM_NAME, key, undefined));
    const newFilters = { ...filters };
    delete newFilters[key];
    dispatch(dataTableActions.setFilters(newFilters));
    await saveDataTableSettings({ name, settings: { filters: newFilters } });
  };

  return (
    <div className="data_table_filter_viewer__wrapper">
      {Object.entries(filters ?? {})
        .filter(
          (filter) =>
            filter[1] !== undefined || filter[1] !== null || filter[1] !== ""
        )
        .map((filter) => {
          const [key, value] = filter;
          const match = filterFields.find((item) => item.name === key);
          const formattedValue = convertFormValueToFormattedValue(value);
          return (
            <div className="data_table_filter_viewer__chip" key={key}>
              <div className="data_table_filter_viewer__chip_title">
                {match?.label}
              </div>
              <div className="data_table_filter_viewer__chip_value">
                {formattedValue}
              </div>
              <Box
                className="data_table_filter_viewer__chip_clear"
                onClick={() => handleClearFilter(key)}
              >
                <CloseIcon />
              </Box>
            </div>
          );
        })}
    </div>
  );
}

export default DataTableFilterViewer;
