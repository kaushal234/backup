import React, { useState } from "react";
import Translator from "bazinga-translator";
import { Typography } from "@mui/material";
import { ITableHeaderItem } from "../../types/ITableHeaderItem";
import Modal from "../Modal/Modal";
import { IDataTableHeaderSelectorFormData } from "../../types/IDataTableHeaderSelectorFormData";
import { IDataTableHeaderItem } from "../../types/IDataTableHeaderItem";
import DataTableHeaderSelectorForm, {
  DATA_TABLE_HEADER_SELECTOR_FORM_NAME,
} from "../DataTableHeaderSelectorForm/DataTableHeaderSelectorForm";
import "./DataTablePersonalization.css";
import DataTableHeaderPosition from "../DataTableHeaderPosition/DataTableHeaderPosition";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import {
  convertDataTableHeaderToSavedHeader,
  saveDataTableSettings,
} from "../../utils/utils";
import { dataTableActions } from "../../reducers/dataTable/dataTableSlice";

interface IProps {
  headers: Array<IDataTableHeaderItem>;
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (headers: Array<ITableHeaderItem>) => void;
  name: string;
}

function DataTablePersonalization(props: IProps) {
  const { headers, isOpen, onClose, onSubmit, name } = props;

  const dispatch = useAppDispatch();

  const { settings, isLoading } = useAppSelector((state) => state.dataTable);

  const formValues: IDataTableHeaderSelectorFormData | undefined =
    useAppSelector(
      (state) => state.form[DATA_TABLE_HEADER_SELECTOR_FORM_NAME]?.values
    );

  const [positions, setPositions] = useState<Array<string>>([]);

  const initialValues: IDataTableHeaderSelectorFormData = {};

  headers.forEach((header) => {
    initialValues[header.value] = !header.isHidden;
  });

  const handleSubmit = async () => {
    const newHeaders = headers.map((header) => ({
      ...header,
      isHidden: !formValues?.[header.value],
      position: positions.findIndex((item) => item === header.value),
    }));
    dispatch(
      dataTableActions.setSetting({
        ...settings,
        headers: convertDataTableHeaderToSavedHeader(newHeaders),
      })
    );
    await saveDataTableSettings({
      name,
      settings: { headers: convertDataTableHeaderToSavedHeader(newHeaders) },
    });
    dispatch(dataTableActions.setIsLoading(false));
    onSubmit(newHeaders);
  };

  return (
    <Modal
      className="data_table_header_personalization__wrapper"
      title={Translator.trans("data_table.personalization.title")}
      isOpen={isOpen}
      onClose={onClose}
    >
      <div className="data_table_header_personalization__sections">
        <div>
          <Typography variant="h6" gutterBottom>
            {Translator.trans("data_table.personalization.visibility")}
          </Typography>
          <DataTableHeaderSelectorForm initialValues={initialValues} />
        </div>
        <div>
          <Typography variant="h6" gutterBottom>
            {Translator.trans("data_table.personalization.position")}
          </Typography>
          <DataTableHeaderPosition onChange={setPositions} headers={headers} />
        </div>
      </div>
      <div className="data_table_header_personalization__button_wrapper">
        <button
          className="btn btn-primary mt-3"
          disabled={!positions.length || isLoading}
          type="button"
          onClick={handleSubmit}
        >
          {Translator.trans("data_table.submit")}
        </button>
      </div>
    </Modal>
  );
}

export default DataTablePersonalization;
