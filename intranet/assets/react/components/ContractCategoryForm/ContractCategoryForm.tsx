import React from "react";
import { InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import { validate } from "../../model/form/contract_category_form/validation";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import Accordion from "../Accordion/Accordion";
import "./ContractCategoryForm.css";
import { IContractCategoryFormData } from "../../types/IContractCategoryFormData";

const formName = "sample_form";

interface IProps {
  onSubmit: (values: IContractCategoryFormData) => void;
}

type IWrappedProps = IProps &
  InjectedFormProps<IContractCategoryFormData, IProps>;

function ContractCategoryForm(props: IWrappedProps) {
  const { submitting, handleSubmit, submitFailed, invalid, onSubmit } = props;

  return (
    <div className="contract_category_form__wrapper">
      <Accordion
        title={Translator.trans("contract_category.edit.accordion.title")}
      >
        <div>
          <form noValidate onSubmit={handleSubmit(onSubmit)}>
            <div className="contract_category_form__section_wrapper">
              <div className="contract_category_form__row">
                <GenericFormComponent
                  type="Field"
                  label={Translator.trans("contract_category.form.name.label")}
                  name="displayedName"
                  required
                />
              </div>
            </div>
            <div className="contract_category_form__right_align">
              <button
                className="btn btn-info mt-3"
                type="submit"
                disabled={submitting || (submitFailed && invalid)}
              >
                {Translator.trans("contract_category.form.action.submit")}
              </button>
            </div>
          </form>
        </div>
      </Accordion>
    </div>
  );
}

export default reduxForm<IContractCategoryFormData, IProps>({
  form: formName,
  enableReinitialize: true,
  validate,
})(ContractCategoryForm);
