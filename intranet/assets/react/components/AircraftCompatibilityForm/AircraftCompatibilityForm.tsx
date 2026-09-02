import React from "react";
import Translator from "bazinga-translator";
import {
  FieldArray,
  InjectedFormProps,
  reduxForm,
  WrappedFieldArrayProps,
} from "redux-form";
import { Col, Row } from "react-bootstrap";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import {
  IAircraftCompatibilityFileFormData,
  IAircraftCompatibilityFormData,
} from "../../types/IAircraftCompatibilityFormData";
import { fetchProduct } from "../../utils/dropdown/product";
import { fetchAircraft } from "../../utils/dropdown/aircraft";
import { AIRCRAFT_COMPATIBILITY_FILE_TYPE_OPTIONS } from "../../constants/constants";
import { validate } from "../../model/form/aircraft_compatibility/validation";

const formName = "aircraft_compatibility_form";

export interface IAircraftCompatibilityFormProps {
  isEdit?: boolean;
  onSubmit: (values: IAircraftCompatibilityFormData) => void;
}

type IWrappedProps = IAircraftCompatibilityFormProps &
  InjectedFormProps<
    IAircraftCompatibilityFormData,
    IAircraftCompatibilityFormProps
  >;

function InternalFileArray({
  fields,
}: WrappedFieldArrayProps<IAircraftCompatibilityFileFormData>) {
  return (
    <div>
      <button
        className="btn btn-info mb-3"
        type="button"
        onClick={() =>
          fields.push({
            type: { value: "", label: "" },
            file: null,
          })
        }
      >
        {Translator.trans("aircraft_compatibility.action.add_file")}
      </button>
      <Row>
        {fields.map((name, index) => (
          <Col md="6" key={name}>
            <div className="card mb-3">
              <div className="card-header">
                <Row>
                  <Col md="6">
                    {Translator.trans("aircraft_compatibility.fields.file")} #
                    {index + 1}
                  </Col>
                  <Col md="6" className="text-end">
                    <button
                      className="btn btn-danger"
                      type="button"
                      onClick={() => fields.remove(index)}
                    >
                      <i className="fa fa-trash" />{" "}
                    </button>
                  </Col>
                </Row>
              </div>
              <div className="card-body">
                <GenericFormComponent
                  type="SingleSelectStaticDropdown"
                  label={Translator.trans("aircraft_compatibility.fields.type")}
                  name={`${name}.type`}
                  list={AIRCRAFT_COMPATIBILITY_FILE_TYPE_OPTIONS}
                  required
                />
                <GenericFormComponent
                  type="FileInput"
                  label={Translator.trans("mis_project.fields.upload_file")}
                  name={`${name}.file`}
                  required
                />
              </div>
            </div>
          </Col>
        ))}
      </Row>
    </div>
  );
}

function AircraftCompatibilityForm(props: IWrappedProps) {
  const { submitting, handleSubmit, submitFailed, invalid, onSubmit, isEdit } =
    props;

  return (
    <div>
      <div>
        <form noValidate onSubmit={handleSubmit(onSubmit)}>
          <Row>
            <Col md="6" ld="12">
              <GenericFormComponent
                type="MutliSelectAutoCompleteDropdown"
                label={Translator.trans("catalogue.family.products")}
                name="products"
                fetchList={fetchProduct}
                required
              />
            </Col>
            <Col md="6" ld="12">
              <GenericFormComponent
                type="MutliSelectAutoCompleteDropdown"
                label={Translator.trans(
                  "aircraft_compatibility.fields.aircrafts"
                )}
                name="aircrafts"
                fetchList={fetchAircraft}
                triggerAtChar={0}
                required
              />
            </Col>
          </Row>
          {!isEdit && <FieldArray name="files" component={InternalFileArray} />}
          <Row />
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
    </div>
  );
}

export default reduxForm<
  IAircraftCompatibilityFormData,
  IAircraftCompatibilityFormProps
>({
  form: formName,
  enableReinitialize: true,
  validate,
})(AircraftCompatibilityForm);
