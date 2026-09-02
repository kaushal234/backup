import React from "react";
import "./GenericFilterForm.css";
import { InjectedFormProps, reduxForm } from "redux-form";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import { IGenericFilterFormSubmissionData } from "../../types/IGenericFilterFormSubmissionData";
import { IGenericDatePickerProps } from "../GenericDateTimePicker/GenericDateTimePicker";
import { IGenericFieldProps } from "../GenericField/GenericField";
import { IGenericMutliSelectAutoCompleteDropdownProps } from "../GenericMultiSelectAutoCompleteDropdown/GenericMultiSelectAutoCompleteDropdown";
import { IGenericMutliSelectDynamicDropdownProps } from "../GenericMultiSelectDynamicDropdown/GenericMultiSelectDynamicDropdown";
import { IGenericMutliSelectStaticDropdownProps } from "../GenericMultiSelectStaticDropdown/GenericMultiSelectStaticDropdown";
import { IGenericSingleSelectAutoCompleteDropdownProps } from "../GenericSingleSelectAutoCompleteDropdown/GenericSingleSelectAutoCompleteDropdown";
import { IGenericSingleSelectDynamicDropdownProps } from "../GenericSingleSelectDynamicDropdown/GenericSingleSelectDynamicDropdown";
import { IGenericSingleSelectStaticDropdownProps } from "../GenericSingleSelectStaticDropdown/GenericSingleSelectStaticDropdown";
import { IGenericFilterFormData } from "../../types/IGenericFilterFormData";
import { convertFilterSubmissionDataToFormData } from "../../utils/utils";

export const GENERIC_FILTER_FORM_NAME = "generic_filter_form";

interface IField extends IGenericFieldProps {
  type: "Field";
}

interface ISingleSelectStaticDropdown
  extends IGenericSingleSelectStaticDropdownProps {
  type: "SingleSelectStaticDropdown";
}

interface ISingleSelectDynamicDropdown
  extends IGenericSingleSelectDynamicDropdownProps {
  type: "SingleSelectDynamicDropdown";
}

interface IMutliSelectStaticDropdown
  extends IGenericMutliSelectStaticDropdownProps {
  type: "MutliSelectStaticDropdown";
}

interface IMutliSelectDynamicDropdown
  extends IGenericMutliSelectDynamicDropdownProps {
  type: "MutliSelectDynamicDropdown";
}

interface ISingleSelectAutoCompleteDropdown
  extends IGenericSingleSelectAutoCompleteDropdownProps {
  type: "SingleSelectAutoCompleteDropdown";
}

interface IMutliSelectAutoCompleteDropdown
  extends IGenericMutliSelectAutoCompleteDropdownProps {
  type: "MutliSelectAutoCompleteDropdown";
}

interface IDatePicker extends IGenericDatePickerProps {
  type: "DatePicker";
}

export type IGenericFilterField =
  | IField
  | ISingleSelectStaticDropdown
  | ISingleSelectDynamicDropdown
  | IMutliSelectStaticDropdown
  | IMutliSelectDynamicDropdown
  | ISingleSelectAutoCompleteDropdown
  | IMutliSelectAutoCompleteDropdown
  | IDatePicker;

interface IProps {
  filterFields: Array<IGenericFilterField>;
  onFormSubmit: (values: IGenericFilterFormData) => void;
  submitButtonText: string;
  isSubmitDisabled?: boolean;
}

export type IGenericFilterFormWrappedProps = IProps &
  InjectedFormProps<IGenericFilterFormSubmissionData, IProps>;

function GenericFilterForm(props: IGenericFilterFormWrappedProps) {
  const {
    submitting,
    handleSubmit,
    submitFailed,
    invalid,
    onFormSubmit,
    filterFields,
    submitButtonText,
    isSubmitDisabled,
  } = props;

  const onFilterFormSubmit = async (
    values: IGenericFilterFormSubmissionData
  ) => {
    const result = convertFilterSubmissionDataToFormData(values);
    await onFormSubmit(result);
  };

  if (!filterFields.length) return null;

  return (
    <form noValidate onSubmit={handleSubmit(onFilterFormSubmit)}>
      <div className="generic_filter_form__wrapper">
        {filterFields.map((field) => (
          <GenericFormComponent {...field} key={field.name ?? ""} />
        ))}
      </div>
      <div className="generic_filter_form__button_wrapper">
        <button
          className="btn btn-info mt-3"
          type="submit"
          disabled={submitting || (submitFailed && invalid) || isSubmitDisabled}
        >
          {submitButtonText}
        </button>
      </div>
    </form>
  );
}

export default reduxForm<IGenericFilterFormSubmissionData, IProps>({
  form: GENERIC_FILTER_FORM_NAME,
  enableReinitialize: true,
})(GenericFilterForm);
