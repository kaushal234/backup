import React, { useEffect, useState } from "react";
import { Field } from "redux-form";
import Translator from "bazinga-translator";
import { renderReactVerticalSelect } from "../Forms/Elements";
import { IDropdownItem } from "../../types/IDropdownItem";
import { useSortedList } from "../../hooks/useSortedList";

export interface IGenericMutliSelectAutoCompleteDropdownProps {
  placeholder?: string;
  name: string;
  label?: string;
  required?: boolean;
  fetchList: (inputValue: string) => Promise<Array<IDropdownItem>>;
  triggerAtChar?: number;
  labelTooltip?: string;
  onChange?: (event: Array<IDropdownItem>, value: Array<IDropdownItem>) => void;
}

// Form Value => Array<IDropdownItem> | undefined
function GenericMultiSelectAutoCompleteDropdown(
  props: IGenericMutliSelectAutoCompleteDropdownProps
) {
  const {
    placeholder = Translator.trans("form.auto_complete.placeholder"),
    name,
    label,
    required,
    fetchList,
    triggerAtChar = 3,
    labelTooltip,
    onChange,
  } = props;

  const [list, setList] = useState<Array<IDropdownItem>>([]);
  const [isLoading, setIsLoading] = useState(false);

  const updateList = async (input: string) => {
    setIsLoading(true);
    const newList = await fetchList(input);
    setList(newList);
    setIsLoading(false);
  };

  const handleChange = (value: Array<IDropdownItem>) => {
    onChange?.(value, value);
  };

  const handleInputChange = (input: string) => {
    if (input === undefined || input.length < triggerAtChar) {
      return;
    }
    updateList(input);
  };

  useEffect(() => {
    handleInputChange("");
  }, []);

  const sortedList = useSortedList({ list });

  return (
    <div
      className={`generic_multi_select_auto_complete_dropdown__wrapper ${name}`}
    >
      <Field
        isMulti
        options={sortedList}
        placeholder={placeholder || ""}
        name={name}
        component={renderReactVerticalSelect}
        label={label}
        required={required}
        isLoadingExternally={isLoading}
        onInputChange={handleInputChange}
        showError
        labelTooltip={labelTooltip}
        props={{
          onChange: onChange ? handleChange : undefined,
        }}
      />
    </div>
  );
}

export default GenericMultiSelectAutoCompleteDropdown;
