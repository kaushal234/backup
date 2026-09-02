import React from "react";
import { Field } from "redux-form";
import Translator from "bazinga-translator";
import { renderReactVerticalSelect } from "../Forms/Elements";
import { IDropdownItem } from "../../types/IDropdownItem";
import { useSortedList } from "../../hooks/useSortedList";
import { IGroupedDropdownItem } from "../../types/IGroupedDropdownItem";

export interface IGenericMutliSelectStaticDropdownProps {
  list: Array<IDropdownItem>;
  placeholder?: string;
  name: string;
  label?: string;
  required?: boolean;
  labelTooltip?: string;
  disabled?: boolean;
  onChange?: (event: Array<IDropdownItem>, value: Array<IDropdownItem>) => void;
  isLoadingExternally?: boolean;
  onInputChange?: (search: string) => void;
  isClearable?: boolean;
  groups?: Array<IGroupedDropdownItem>;
  groupsOverride?: boolean;
  async?: boolean;
  id?: string;
}

// Form Value => Array<IDropdownItem> | undefined
function GenericMultiSelectStaticDropdown(
  props: IGenericMutliSelectStaticDropdownProps
) {
  const {
    list,
    placeholder = Translator.trans("form.auto_complete.placeholder"),
    name,
    label,
    required,
    labelTooltip,
    disabled,
    onInputChange,
    isClearable,
    isLoadingExternally,
    onChange,
    groups,
    groupsOverride,
    async,
    id,
  } = props;

  const sortedList = useSortedList({ list });

  const handleChange = (value: Array<IDropdownItem>) => {
    onChange?.(value, value);
  };

  return (
    <div className={`generic_multi_select_static_dropdown__wrapper ${name}`}>
      <Field
        isMulti
        options={sortedList}
        placeholder={placeholder || ""}
        name={name}
        component={renderReactVerticalSelect}
        label={label}
        required={required}
        showError
        labelTooltip={labelTooltip}
        isDisabled={disabled}
        onInputChange={onInputChange}
        isClearable={isClearable}
        isLoadingExternally={isLoadingExternally}
        props={{
          onChange: onChange ? handleChange : undefined,
        }}
        groups={groups}
        groupsOverride={groupsOverride}
        async={async}
        id={id}
      />
    </div>
  );
}

export default GenericMultiSelectStaticDropdown;
