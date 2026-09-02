import React from "react";
import { InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import { validate } from "../../model/form/contract_category_form/validation";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import Accordion from "../Accordion/Accordion";
import "./ContractSubCategoryForm.css";
import { IContractSubCategoryFormData } from "../../types/IContractSubCategoryFormData";

const formName = "sample_form";

interface IProps {
  onSubmit: (values: IContractSubCategoryFormData) => void;
}

type IWrappedProps = IProps &
  InjectedFormProps<IContractSubCategoryFormData, IProps>;

function ContractSubCategoryForm(props: IWrappedProps) {
  const { submitting, handleSubmit, submitFailed, invalid, onSubmit } = props;

  return (
    <div className="contract_sub_category_form__wrapper">
      <Accordion
        title={Translator.trans("contract_sub_category.edit.accordion.title")}
      >
        <div>
          <form noValidate onSubmit={handleSubmit(onSubmit)}>
            <div className="contract_sub_category_form__section_wrapper">
              <div className="contract_sub_category_form__row">
                <GenericFormComponent
                  type="Field"
                  label={Translator.trans(
                    "contract_sub_category.form.name.label"
                  )}
                  name="displayedName"
                  required
                />
              </div>
            </div>
            <div className="contract_sub_category_form__right_align">
              <button
                className="btn btn-info mt-3"
                type="submit"
                disabled={submitting || (submitFailed && invalid)}
              >
                {Translator.trans("contract_sub_category.form.action.submit")}
              </button>
            </div>
          </form>
        </div>
      </Accordion>
    </div>
  );
}

export default reduxForm<IContractSubCategoryFormData, IProps>({
  form: formName,
  enableReinitialize: true,
  validate,
})(ContractSubCategoryForm);
