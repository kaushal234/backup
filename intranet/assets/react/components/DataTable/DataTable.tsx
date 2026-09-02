import React, { useEffect, useState } from "react";
import { change } from "redux-form";
import { GENERIC_FILTER_FORM_NAME } from "../GenericFilterForm/GenericFilterForm";
import { useAppDispatch } from "../../hooks/hooks";
import FullScreenLoader from "../FullScreenLoader/FullScreenLoader";
import { dataTableActions } from "../../reducers/dataTable/dataTableSlice";
import { getDataTableName } from "../../utils/utils";
import { getAllUserSetting } from "../../api/getAllUserSetting";
import { IDataTableSavedSetting } from "../../types/IDataTableSavedSetting";
import DataTableFeature, {
  IDataTableFeatureProps,
} from "../DataTableFeature/DataTableFeature";

function DataTable(props: IDataTableFeatureProps) {
  const {
    title,
    rows,
    rowCount,
    headers,
    fetchData,
    fetchDownloadData,
    filterFields,
    disableHeaderSelector,
    name,
    filterInitialValues,
  } = props;

  const dispatch = useAppDispatch();

  const [isSettingsFetched, setIsSettingsFetched] = useState(false);

  const fetchPersonalization = async () => {
    dispatch(dataTableActions.setIsLoading(true));
    const response = await getAllUserSetting();
    dispatch(dataTableActions.setIsLoading(false));
    if (response.data) {
      const userSetting = response.data["hydra:member"].find(
        (item) => item.name === getDataTableName(name)
      )?.settings as IDataTableSavedSetting | undefined;

      dispatch(dataTableActions.setSetting(userSetting ?? null));

      const filters = userSetting?.filters ?? filterInitialValues;
      if (filters) {
        dispatch(dataTableActions.setFilters(filters));
        Object.entries(filters).forEach((filter) => {
          dispatch(change(GENERIC_FILTER_FORM_NAME, filter[0], filter[1]));
        });
      }

      if (userSetting?.sortModel) {
        dispatch(dataTableActions.setSortModal(userSetting.sortModel));
      }

      if (userSetting?.pagination) {
        dispatch(dataTableActions.setPagination(userSetting.pagination));
      }
    }
    setIsSettingsFetched(true);
  };

  useEffect(() => {
    fetchPersonalization();
  }, []);

  if (!isSettingsFetched) return null;

  return (
    <div className="data_table__wrapper">
      <FullScreenLoader />
      <DataTableFeature
        title={title}
        rows={rows}
        rowCount={rowCount}
        headers={headers}
        fetchData={fetchData}
        fetchDownloadData={fetchDownloadData}
        filterFields={filterFields}
        disableHeaderSelector={disableHeaderSelector}
        name={name}
        filterInitialValues={filterInitialValues}
      />
    </div>
  );
}

export default DataTable;
