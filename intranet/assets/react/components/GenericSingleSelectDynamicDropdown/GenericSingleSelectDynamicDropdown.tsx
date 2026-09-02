import React, { useEffect, useState } from "react";
import { Field } from "redux-form";
import Translator from "bazinga-translator";
import { renderReactVerticalSelect } from "../Forms/Elements";
import { IDropdownItem } from "../../types/IDropdownItem";
import { useSortedList } from "../../hooks/useSortedList";

export interface IGenericSingleSelectDynamicDropdownProps {
  fetchList: () => Promise<Array<IDropdownItem>>;
  placeholder?: string;
  name: string;
  label?: string;
  required?: boolean;
  labelTooltip?: string;
  onChange?: (event: IDropdownItem, value: IDropdownItem) => void;
}

// Form Value => IDropdownItem | null | undefined
function GenericSingleSelectDynamicDropdown(
  props: IGenericSingleSelectDynamicDropdownProps
) {
  const {
    fetchList,
    placeholder = Translator.trans("form.auto_complete.placeholder"),
    name,
    label,
    required,
    labelTooltip,
    onChange,
  } = props;

  const [options, setOptions] = useState<Array<IDropdownItem>>([]);
  const [isLoading, setIsLoading] = useState(false);

  const updateList = async () => {
    if (fetchList) {
      setIsLoading(true);
      const newList = await fetchList();
      setOptions(newList);
      setIsLoading(false);
    }
  };

  useEffect(() => {
    updateList();
  }, []);

  const sortedOptions = useSortedList({ list: options });

  const handleChange = (value: IDropdownItem) => {
    onChange?.(value, value);
  };

  return (
    <div className={`generic_single_select_dynamic_dropdown__wrapper ${name}`}>
      <Field
        options={sortedOptions}
        placeholder={placeholder || ""}
        name={name}
        component={renderReactVerticalSelect}
        label={label}
        required={required}
        isLoadingExternally={isLoading}
        showError
        labelTooltip={labelTooltip}
        props={{
          onChange: onChange ? handleChange : undefined,
        }}
      />
    </div>
  );
}

export default GenericSingleSelectDynamicDropdown;
