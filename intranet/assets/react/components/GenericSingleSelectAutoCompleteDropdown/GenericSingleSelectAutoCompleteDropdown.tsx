import React, { useEffect, useState } from "react";
import { Field } from "redux-form";
import Translator from "bazinga-translator";
import { renderReactVerticalSelect } from "../Forms/Elements";
import { IDropdownItem } from "../../types/IDropdownItem";
import { useSortedList } from "../../hooks/useSortedList";

interface IFormatOptionLabelMeta {
  context: string;
  inputValue: string;
}

interface IFormatOptionLabel {
  (option: IDropdownItem, meta: IFormatOptionLabelMeta): React.ReactNode;
}

export interface IGenericSingleSelectAutoCompleteDropdownProps {
  placeholder?: string;
  name: string;
  label?: string;
  required?: boolean;
  fetchList: (inputValue: string) => Promise<Array<IDropdownItem>>;
  triggerAtChar?: number;
  labelTooltip?: string;
  isClearable?: boolean;
  onChange?: (event: IDropdownItem, value: IDropdownItem) => void;
  formatOptionLabel?: IFormatOptionLabel;
}

// Form Value => IDropdownItem | null | undefined
function GenericSingleSelectAutoCompleteDropdown(
  props: IGenericSingleSelectAutoCompleteDropdownProps
) {
  const {
    placeholder = Translator.trans("form.auto_complete.placeholder"),
    name,
    label,
    required,
    fetchList,
    triggerAtChar = 3,
    labelTooltip,
    isClearable,
    onChange,
    formatOptionLabel,
  } = props;

  const [list, setList] = useState<Array<IDropdownItem>>([]);
  const [isLoading, setIsLoading] = useState(false);

  const updateList = async (input: string) => {
    setIsLoading(true);
    const newList = await fetchList(input);
    setList(newList);
    setIsLoading(false);
  };

  const handleInputChange = (input: string) => {
    if (input === undefined || input.length < triggerAtChar) {
      return;
    }
    updateList(input);
  };

  const handleChange = (value: IDropdownItem) => {
    onChange?.(value, value);
  };

  useEffect(() => {
    handleInputChange("");
  }, []);

  const sortedList = useSortedList({ list });

  return (
    <div
      className={`generic_single_select_auto_complete_dropdown__wrapper ${name}`}
    >
      <Field
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
        isClearable={isClearable}
        formatOptionLabel={formatOptionLabel}
        props={{
          onChange: onChange ? handleChange : undefined,
        }}
      />
    </div>
  );
}

export default GenericSingleSelectAutoCompleteDropdown;
