import React, { useEffect, useState } from "react";
import "./DataTableFeature.css";
import { Collapse, Divider, IconButton } from "@mui/material";
import FilterAltOffIcon from "@mui/icons-material/FilterAltOff";
import { change, reset } from "redux-form";
import Translator from "bazinga-translator";
import WysiwygIcon from "@mui/icons-material/Wysiwyg";
import DownloadIcon from "@mui/icons-material/Download";
import GenericFilterForm, {
  GENERIC_FILTER_FORM_NAME,
  IGenericFilterField,
} from "../GenericFilterForm/GenericFilterForm";
import { IDataTableFetchParams } from "../../types/IDataTableFetchRequest";
import DataTableFilterViewer from "../DataTableFilterViewer/DataTableFilterViewer";
import { IGenericFilterFormSubmissionData } from "../../types/IGenericFilterFormSubmissionData";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import Accordion from "../Accordion/Accordion";
import { dataTableActions } from "../../reducers/dataTable/dataTableSlice";
import {
  convertFilterSubmissionDataToFormData,
  getDataTableName,
  mergeDataTableHeaders,
  saveDataTableSettings,
} from "../../utils/utils";
import DataTableView, {
  IDataTableViewProps,
} from "../DataTableView/DataTableView";
import { IDataTableHeaderItem } from "../../types/IDataTableHeaderItem";
import DataTableDownloadModal from "../DataTableDownloadModal/DataTableDownloadModal";
import DataTablePersonalization from "../DataTablePersonalization/DataTablePersonalization";
import { ISaveDataTableSettingApiPayload } from "../../types/ISaveDataTableSettingApiPayload";
import { DEFAULT_PAGINATION } from "../../constants/constants";
import { addOrUpdateUserSettingByName } from "../../api/addOrUpdateUserSettingByName";
import { IGenericFilterFormData } from "../../types/IGenericFilterFormData";

export interface IDataTableFeatureProps extends IDataTableViewProps {
  title: string;
  filterFields?: Array<IGenericFilterField>;
  name: string;
  fetchData: (data: IDataTableFetchParams) => Promise<void>;
  disableHeaderSelector?: boolean;
  filterInitialValues?: IGenericFilterFormSubmissionData;
  fetchDownloadData?: (
    data: IGenericFilterFormData
  ) => Promise<Array<Array<string | null>>>;
}

function DataTableFeature(props: IDataTableFeatureProps) {
  const {
    title,
    rows,
    rowCount,
    headers: initialHeaders,
    fetchData,
    fetchDownloadData,
    filterFields,
    disableHeaderSelector,
    name,
    filterInitialValues,
  } = props;

  const dispatch = useAppDispatch();

  const { sortModel, pagination, filters, settings, isLoading } =
    useAppSelector((state) => state.dataTable);

  const formValues: IGenericFilterFormSubmissionData | undefined =
    useAppSelector((state) => state.form[GENERIC_FILTER_FORM_NAME]?.values);

  const [isDownloadModalOpen, setIsDownloadModalOpen] = useState(false);
  const [isHeaderSelectorOpen, setIsHeaderSelectorOpen] = useState(false);
  const [isFilterSectionVisible, setIsFilterSectionVisible] = useState(false);
  const [headers, setHeaders] = useState(
    mergeDataTableHeaders(initialHeaders, settings?.headers ?? [])
  );

  const appliedFiltersCount = Object.values(filters ?? {}).filter(
    (filter) => filter !== undefined || filter !== null || filter !== ""
  ).length;

  const handleHeaderSelect = (newHeaders: Array<IDataTableHeaderItem>) => {
    setHeaders(newHeaders);
    setIsHeaderSelectorOpen(false);
  };

  const handleFilterSubmit = async () => {
    dispatch(
      dataTableActions.setFilters(JSON.parse(JSON.stringify(formValues)))
    );
    await saveDataTableSettings({ name, settings: { filters: formValues } });
  };

  const handleClearAllFilters = async () => {
    dispatch(reset(GENERIC_FILTER_FORM_NAME));
    dispatch(dataTableActions.setFilters({}));
    await saveDataTableSettings({ name, settings: { filters: {} } });
  };

  const handleResetSettings = async () => {
    if (isLoading) return;
    dispatch(dataTableActions.setIsLoading(true));

    const personalization: ISaveDataTableSettingApiPayload = {
      name: getDataTableName(name),
      settings: {
        pagination: DEFAULT_PAGINATION,
      },
    };
    await addOrUpdateUserSettingByName(personalization);

    setHeaders(initialHeaders);
    dispatch(dataTableActions.setPagination(DEFAULT_PAGINATION));
    dispatch(dataTableActions.setSetting(null));
    dispatch(dataTableActions.setSortModal(null));
    dispatch(dataTableActions.setIsLoading(true));

    const initialFilters = filterInitialValues ?? {};
    dispatch(reset(GENERIC_FILTER_FORM_NAME));
    dispatch(dataTableActions.setFilters(initialFilters));
    Object.entries(initialFilters).forEach((filter) => {
      dispatch(change(GENERIC_FILTER_FORM_NAME, filter[0], filter[1]));
    });
  };

  const handleFetch = async () => {
    dispatch(dataTableActions.setIsLoading(true));
    const params: IDataTableFetchParams = {
      pagination: {
        itemsPerPage: pagination.itemsPerPage,
        page: (pagination.page ?? 0) + 1,
        pagination: true,
      },
      filters: convertFilterSubmissionDataToFormData(filters),
    };
    if (sortModel) {
      const orderName = `order[${sortModel?.field}]`;
      params.sort = {
        [orderName]: sortModel?.sort ?? "asc",
      };
    }
    await fetchData(params);
    dispatch(dataTableActions.setIsLoading(false));
  };

  useEffect(() => {
    handleFetch();
  }, [filters, sortModel, pagination]);

  return (
    <div className="data_table_feature__wrapper">
      <Accordion
        title={title}
        onFilterClick={() => setIsFilterSectionVisible((prev) => !prev)}
        onResetClick={handleResetSettings}
      >
        <Collapse in={isFilterSectionVisible}>
          <div>
            <GenericFilterForm
              filterFields={filterFields ?? []}
              onFormSubmit={handleFilterSubmit}
              submitButtonText={Translator.trans("data_table.filter")}
              isSubmitDisabled={isLoading}
            />
          </div>
          <Divider className="data_table_feature__divider" />
        </Collapse>
        <div>
          <DataTablePersonalization
            isOpen={isHeaderSelectorOpen}
            onClose={() => setIsHeaderSelectorOpen(false)}
            headers={headers}
            onSubmit={handleHeaderSelect}
            name={name}
          />
          <DataTableDownloadModal
            isOpen={isDownloadModalOpen}
            onClose={() => setIsDownloadModalOpen(false)}
            headers={headers}
            fetchDownloadData={fetchDownloadData}
          />
          <div className="data_table_feature__actions_wrapper">
            <div className="data_table_feature__filters_wrapper">
              <DataTableFilterViewer
                filterFields={filterFields ?? []}
                name={name}
              />
            </div>
            {appliedFiltersCount !== 0 && (
              <div className="data_table_feature__action_wrapper">
                <IconButton onClick={handleClearAllFilters}>
                  <FilterAltOffIcon />
                </IconButton>
              </div>
            )}

            {fetchDownloadData && (
              <div className="data_table_feature__action_wrapper">
                <IconButton onClick={() => setIsDownloadModalOpen(true)}>
                  <DownloadIcon />
                </IconButton>
              </div>
            )}
            {!disableHeaderSelector && (
              <div className="data_table_feature__action_wrapper">
                <IconButton onClick={() => setIsHeaderSelectorOpen(true)}>
                  <WysiwygIcon />
                </IconButton>
              </div>
            )}
          </div>
          <DataTableView
            name={name}
            headers={headers}
            rows={rows}
            rowCount={rowCount}
            noContentMessage={Translator.trans("data_table.no_items")}
          />
        </div>
      </Accordion>
    </div>
  );
}

export default DataTableFeature;
