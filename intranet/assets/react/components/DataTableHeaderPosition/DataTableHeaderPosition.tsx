import React, { useEffect, useState } from "react";
import { IDataTableHeaderSelectorFormData } from "../../types/IDataTableHeaderSelectorFormData";
import { DATA_TABLE_HEADER_SELECTOR_FORM_NAME } from "../DataTableHeaderSelectorForm/DataTableHeaderSelectorForm";
import { useAppSelector } from "../../hooks/hooks";
import PositionList from "../PositionList/PositionList";
import "./DataTableHeaderPosition.css";
import { IDataTableHeaderItem } from "../../types/IDataTableHeaderItem";

const getListFromHeaders = (headers: Array<IDataTableHeaderItem>) => {
  const list: Array<string> = [];
  headers.forEach((header) => {
    if (header.position === undefined) {
      list.push(header.value);
    } else if (header.position !== -1) {
      list[header.position] = header.value;
    }
  });
  return list;
};

interface IProps {
  onChange: (list: Array<string>) => void;
  headers: Array<IDataTableHeaderItem>;
}

function DataTableHeaderPosition(props: IProps) {
  const { onChange, headers } = props;

  const [isFirstRender, setIsFirstRender] = useState(true);
  const [list, setList] = useState(getListFromHeaders(headers));

  const formValues: IDataTableHeaderSelectorFormData | undefined =
    useAppSelector(
      (state) => state.form[DATA_TABLE_HEADER_SELECTOR_FORM_NAME]?.values
    );

  useEffect(() => {
    if (isFirstRender) {
      setList(getListFromHeaders(headers));
      setIsFirstRender(false);
    } else {
      const newList = Object.entries(formValues ?? {})
        .filter((item) => item[1])
        .map((item) => item[0]);
      setList(newList);
    }
  }, [formValues]);

  return (
    <div className="data_table_header_position__wrapper">
      <PositionList list={list} onChange={onChange} />
    </div>
  );
}

export default DataTableHeaderPosition;
