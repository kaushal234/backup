import React, { useEffect, useState } from "react";
import { Field } from "redux-form";
import Translator from "bazinga-translator";
import { renderReactVerticalSelect } from "../Forms/Elements";
import { IDropdownItem } from "../../types/IDropdownItem";
import { IPaginatedFetchFunction } from "../../types/IPaginatedDropdown";
import { IGenericSingleSelectDynamicDropdownProps } from "../GenericSingleSelectDynamicDropdown/GenericSingleSelectDynamicDropdown";

export interface IGenericSingleSelectPaginatedDropdownProps
  extends Omit<IGenericSingleSelectDynamicDropdownProps, "fetchList"> {
  fetchPage: IPaginatedFetchFunction;
}

function GenericSingleSelectPaginatedDropdown(
  props: IGenericSingleSelectPaginatedDropdownProps
) {
  const {
    fetchPage,
    placeholder = Translator.trans("form.auto_complete.placeholder"),
    name,
    label,
    required,
    labelTooltip,
    onChange,
  } = props;

  const [options, setOptions] = useState<Array<IDropdownItem>>([]);
  const [nextUrl, setNextUrl] = useState<string | undefined>(undefined);
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    const loadFirstPage = async () => {
      setIsLoading(true);
      const result = await fetchPage();
      setOptions(result.items);
      setNextUrl(result.nextUrl);
      setIsLoading(false);
    };
    loadFirstPage();
  }, []);

  const loadNextPage = async () => {
    if (!nextUrl || isLoading) return;
    setIsLoading(true);
    const result = await fetchPage(nextUrl);
    setOptions((prev) => [...prev, ...result.items]);
    setNextUrl(result.nextUrl);
    setIsLoading(false);
  };

  const handleChange = (value: IDropdownItem) => {
    onChange?.(value, value);
  };

  return (
    <div className={`generic_single_select_dynamic_dropdown__wrapper ${name}`}>
      <Field
        options={options}
        placeholder={placeholder || ""}
        name={name}
        component={renderReactVerticalSelect}
        label={label}
        required={required}
        isLoadingExternally={isLoading}
        showError
        labelTooltip={labelTooltip}
        onMenuScrollToBottom={loadNextPage}
        props={{
          onChange: onChange ? handleChange : undefined,
        }}
      />
    </div>
  );
}

export default GenericSingleSelectPaginatedDropdown;
