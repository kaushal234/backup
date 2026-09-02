import React from "react";
import Translator from "bazinga-translator";
import { InjectedFormProps, reduxForm } from "redux-form";
import { Col, Row } from "react-bootstrap";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import { IAircraftCompatibilityFileFormData } from "../../types/IAircraftCompatibilityFormData";
import { AIRCRAFT_COMPATIBILITY_FILE_TYPE_OPTIONS } from "../../constants/constants";

const formName = "aircraft_compatibility_file_form";

export interface IAircraftCompatibilityFileFormProps {
  onSubmit: (values: IAircraftCompatibilityFileFormData) => void;
}

type IWrappedProps = IAircraftCompatibilityFileFormProps &
  InjectedFormProps<
    IAircraftCompatibilityFileFormData,
    IAircraftCompatibilityFileFormProps
  >;

function AircraftCompatibilityFileForm(props: IWrappedProps) {
  const { submitting, handleSubmit, submitFailed, invalid, onSubmit } = props;

  return (
    <div>
      <div>
        <form noValidate onSubmit={handleSubmit(onSubmit)}>
          <Row>
            <Col md="6">
              <div className="card mb-3">
                <div className="card-header">
                  <Row>
                    <Col md="6">Add File</Col>
                  </Row>
                </div>
                <div className="card-body">
                  <GenericFormComponent
                    type="SingleSelectStaticDropdown"
                    label={Translator.trans(
                      "aircraft_compatibility.fields.type"
                    )}
                    name="type"
                    list={AIRCRAFT_COMPATIBILITY_FILE_TYPE_OPTIONS}
                    required
                  />
                  <GenericFormComponent
                    type="FileInput"
                    label={Translator.trans("mis_project.fields.upload_file")}
                    name="file"
                    required
                  />
                </div>
              </div>
            </Col>
          </Row>
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
  IAircraftCompatibilityFileFormData,
  IAircraftCompatibilityFileFormProps
>({
  form: formName,
  enableReinitialize: true,
})(AircraftCompatibilityFileForm);
