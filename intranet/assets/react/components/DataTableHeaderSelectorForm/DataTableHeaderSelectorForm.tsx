import React from "react";
import { InjectedFormProps, reduxForm } from "redux-form";
import { IDataTableHeaderSelectorFormData } from "../../types/IDataTableHeaderSelectorFormData";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import { useAppSelector } from "../../hooks/hooks";

export const DATA_TABLE_HEADER_SELECTOR_FORM_NAME =
  "data_table_header_selector_form";

type IProps = unknown;

type IWrappedProps = IProps &
  InjectedFormProps<IDataTableHeaderSelectorFormData, IProps>;

function DataTableHeaderSelectorForm(props: IWrappedProps) {
  const { form } = props;

  const formValues: IDataTableHeaderSelectorFormData | undefined =
    useAppSelector((state) => state.form[form]?.values);

  return (
    <form noValidate>
      {Object.keys(formValues ?? {}).map((formValue) => (
        <GenericFormComponent
          key={formValue}
          type="Checkbox"
          label={formValue}
          name={formValue}
        />
      ))}
    </form>
  );
}

export default reduxForm<IDataTableHeaderSelectorFormData, IProps>({
  form: DATA_TABLE_HEADER_SELECTOR_FORM_NAME,
  enableReinitialize: true,
})(DataTableHeaderSelectorForm);
