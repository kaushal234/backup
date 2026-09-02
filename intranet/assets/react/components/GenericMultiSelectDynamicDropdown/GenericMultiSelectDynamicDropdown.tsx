import React, { useEffect, useState } from "react";
import { Field } from "redux-form";
import Translator from "bazinga-translator";
import { renderReactVerticalSelect } from "../Forms/Elements";
import { IDropdownItem } from "../../types/IDropdownItem";
import { useSortedList } from "../../hooks/useSortedList";

export interface IGenericMutliSelectDynamicDropdownProps {
  fetchList: () => Promise<Array<IDropdownItem>>;
  placeholder?: string;
  name: string;
  label?: string;
  required?: boolean;
  labelTooltip?: string;
}

// Form Value => Array<IDropdownItem> | undefined
function GenericMultiSelectDynamicDropdown(
  props: IGenericMutliSelectDynamicDropdownProps
) {
  const {
    fetchList,
    placeholder = Translator.trans("form.auto_complete.placeholder"),
    name,
    label,
    required,
    labelTooltip,
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

  return (
    <div className={`generic_multi_select_dynamic_dropdown__wrapper ${name}`}>
      <Field
        isMulti
        options={sortedOptions}
        placeholder={placeholder || ""}
        name={name}
        component={renderReactVerticalSelect}
        label={label}
        required={required}
        isLoadingExternally={isLoading}
        showError
        labelTooltip={labelTooltip}
      />
    </div>
  );
}

export default GenericMultiSelectDynamicDropdown;
