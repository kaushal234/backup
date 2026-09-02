import React from "react";
import { Field } from "redux-form";
import Translator from "bazinga-translator";
import { renderReactVerticalSelect } from "../Forms/Elements";
import { IDropdownItem } from "../../types/IDropdownItem";
import { useSortedList } from "../../hooks/useSortedList";
import "./GenericSingleSelectStaticDropdown.css";
import { IGroupedDropdownItem } from "../../types/IGroupedDropdownItem";

export interface IGenericSingleSelectStaticDropdownProps {
  list: Array<IDropdownItem> | Array<IGroupedDropdownItem>;
  placeholder?: string;
  name: string;
  label?: string;
  required?: boolean;
  disabled?: boolean;
  labelTooltip?: string;
  onChange?: (event: IDropdownItem, value: IDropdownItem) => void;
  isLoadingExternally?: boolean;
  onInputChange?: (search: string) => void;
  isClearable?: boolean;
  groups?: Array<IGroupedDropdownItem>;
  groupsOverride?: boolean;
  async?: boolean;
  id?: string;
}

// Form Value => IDropdownItem | null | undefined
function GenericSingleSelectStaticDropdown(
  props: IGenericSingleSelectStaticDropdownProps
) {
  const {
    list,
    placeholder = Translator.trans("form.auto_complete.placeholder"),
    name,
    label,
    required,
    disabled,
    labelTooltip,
    onChange,
    isLoadingExternally,
    onInputChange,
    isClearable,
    groups,
    groupsOverride,
    async,
    id,
  } = props;

  const sortedList = useSortedList({ list });

  const handleChange = (value: IDropdownItem) => {
    onChange?.(value, value);
  };

  return (
    <div
      className={`generic_single_select_static_dropdown__wrapper ${name} 
      ${disabled && "generic_single_select_static_dropdown__disabled"}`}
    >
      <Field
        options={sortedList}
        placeholder={placeholder || ""}
        name={name}
        component={renderReactVerticalSelect}
        label={label}
        required={required}
        showError
        isDisabled={disabled}
        labelTooltip={labelTooltip}
        props={{
          onChange: onChange ? handleChange : undefined,
        }}
        isLoadingExternally={isLoadingExternally}
        onInputChange={onInputChange}
        isClearable={isClearable}
        groups={groups}
        groupsOverride={groupsOverride}
        async={async}
        id={id}
      />
    </div>
  );
}

export default GenericSingleSelectStaticDropdown;
