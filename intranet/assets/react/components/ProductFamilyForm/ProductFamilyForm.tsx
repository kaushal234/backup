import React from "react";
import Translator from "bazinga-translator";
import { InjectedFormProps, reduxForm } from "redux-form";
import { Col, Row } from "react-bootstrap";
import { validate } from "../../model/form/product_family_form/validation";
import Accordion from "../../containers/Accordion/Accordion";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import { fetchAllProductType } from "../../utils/dropdown/productType";
import { fetchAllProductFamilyTags } from "../../utils/dropdown/productFamilyTag";
import { fetchAllManufacturingFactories } from "../../utils/dropdown/manufacturingFactory";
import { IProductFamilyFormData } from "../../types/IProductFamilyFormData";

const formName = "sample_form";

export interface IProductFamilyFormProps {
  isEdit?: boolean;
  onSubmit: (values: IProductFamilyFormData) => void;
}

type IWrappedProps = IProductFamilyFormProps &
  InjectedFormProps<IProductFamilyFormData, IProductFamilyFormProps>;

function ProductFamilyForm(props: IWrappedProps) {
  const { submitting, handleSubmit, submitFailed, invalid, onSubmit, isEdit } =
    props;

  return (
    <div>
      <Accordion
        title={
          isEdit
            ? Translator.trans("catalogue.family.edit_family")
            : Translator.trans("catalogue.family.add_family")
        }
      >
        <div>
          <form noValidate onSubmit={handleSubmit(onSubmit)}>
            <Row md="12" ld="12">
              <Col md="6" ld="6">
                <GenericFormComponent
                  type="Field"
                  label={Translator.trans("catalogue.type.name")}
                  placeholder={Translator.trans("catalogue.type.name_label")}
                  name="name"
                  required
                />
              </Col>

              <Col md="6" ld="6">
                <GenericFormComponent
                  type="SingleSelectDynamicDropdown"
                  label={Translator.trans("catalogue.family.type")}
                  name="productType"
                  fetchList={fetchAllProductType}
                  required
                />
              </Col>
            </Row>
            <Row>
              <Col md="6" ld="6">
                <GenericFormComponent
                  type="MutliSelectAutoCompleteDropdown"
                  label={Translator.trans("fields.tags")}
                  placeholder={Translator.trans("fields.tags")}
                  name="tags"
                  fetchList={fetchAllProductFamilyTags}
                  triggerAtChar={0}
                />
              </Col>
              <Col md="6" ld="6">
                <GenericFormComponent
                  type="MutliSelectAutoCompleteDropdown"
                  label={Translator.trans(
                    "catalogue.family.manufacturing_factories"
                  )}
                  placeholder={Translator.trans(
                    "catalogue.family.manufacturing_factories"
                  )}
                  name="manufacturingFactories"
                  fetchList={fetchAllManufacturingFactories}
                  triggerAtChar={0}
                />
              </Col>
            </Row>

            <Row>
              <Col md="6" lg="6">
                <p className="fw-bold mb-1">
                  {Translator.trans("catalogue.type.public_type")}
                </p>
                <div className="ps-4">
                  <div className="col-3">
                    <GenericFormComponent
                      type="Checkbox"
                      label={Translator.trans("catalogue.type.public_tld")}
                      name="publicForTLD"
                      checkboxFirst
                      fitContent
                    />
                    <GenericFormComponent
                      type="Checkbox"
                      label={Translator.trans("catalogue.type.public_aero")}
                      name="publicForAerospecialties"
                      checkboxFirst
                      fitContent
                    />
                    <GenericFormComponent
                      type="Checkbox"
                      label={Translator.trans("catalogue.type.public_sas")}
                      name="publicForSAS"
                      checkboxFirst
                      fitContent
                    />
                  </div>
                </div>
              </Col>
              <Col md="6" lg="6">
                <p className="fw-bold mb-1">
                  {Translator.trans("catalogue.type.visibility")}
                </p>
                <div className="ps-4">
                  <GenericFormComponent
                    type="Checkbox"
                    label={Translator.trans("catalogue.products.hidden")}
                    name="hidden"
                    fitContent
                    checkboxFirst
                  />
                </div>
              </Col>
            </Row>

            <GenericFormComponent
              type="Field"
              label={Translator.trans("catalogue.type.english")}
              name="englishDescription"
              isTextArea
            />
            <GenericFormComponent
              type="Field"
              label={Translator.trans("catalogue.type.french")}
              name="frenchDescription"
              isTextArea
            />
            <GenericFormComponent
              type="Field"
              label={Translator.trans("catalogue.type.spanish")}
              name="spanishDescription"
              isTextArea
            />
            <GenericFormComponent
              type="Field"
              label={Translator.trans("catalogue.type.portuguese")}
              name="portugueseDescription"
              isTextArea
            />
            <GenericFormComponent
              type="Field"
              label={Translator.trans("catalogue.type.chinese")}
              name="chineseDescription"
              isTextArea
            />
            <GenericFormComponent
              type="Field"
              label={Translator.trans("catalogue.type.japanese")}
              name="japaneseDescription"
              isTextArea
            />
            <GenericFormComponent
              type="Field"
              label={Translator.trans("catalogue.type.german")}
              name="germanDescription"
              isTextArea
            />
            <GenericFormComponent
              type="Field"
              label={Translator.trans("catalogue.type.russian")}
              name="russianDescription"
              isTextArea
            />
            <div className="text-end">
              <button
                className="btn btn-info mt-3"
                type="submit"
                disabled={submitting || (submitFailed && invalid)}
              >
                {Translator.trans("catalogue.submit")}
              </button>
            </div>
          </form>
        </div>
      </Accordion>
    </div>
  );
}

export default reduxForm<IProductFamilyFormData, IProductFamilyFormProps>({
  form: formName,
  enableReinitialize: true,
  validate,
})(ProductFamilyForm);
