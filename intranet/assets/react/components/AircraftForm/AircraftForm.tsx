import React from "react";
import Translator from "bazinga-translator";
import { InjectedFormProps, reduxForm } from "redux-form";
import { Col, Row } from "react-bootstrap";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import { IAircraftFormData } from "../../types/IAircraftFormData";
import { fetchAllManufacturers } from "../../utils/dropdown/manufacturer";
import { validate } from "../../model/form/aircraft/validation";

const formName = "aircraft_form";

export interface IAircraftFormProps {
  onSubmit: (values: IAircraftFormData) => void;
}

type IWrappedProps = IAircraftFormProps &
  InjectedFormProps<IAircraftFormData, IAircraftFormProps>;

function AircraftForm(props: IWrappedProps) {
  const { submitting, handleSubmit, submitFailed, invalid, onSubmit } = props;

  return (
    <div>
      <div>
        <form noValidate onSubmit={handleSubmit(onSubmit)}>
          <Row>
            <Col md="6" ld="12">
              <GenericFormComponent
                type="Field"
                label={Translator.trans("aircraft_compatibility.fields.name")}
                name="name"
                required
              />
            </Col>
            <Col md="6" ld="12">
              <GenericFormComponent
                type="SingleSelectAutoCompleteDropdown"
                label={Translator.trans(
                  "aircraft_compatibility.fields.manufacturer"
                )}
                name="manufacturer"
                fetchList={fetchAllManufacturers}
                triggerAtChar={0}
                required
              />
            </Col>
          </Row>
          <Row />
          <div>
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

export default reduxForm<IAircraftFormData, IAircraftFormProps>({
  form: formName,
  enableReinitialize: true,
  validate,
})(AircraftForm);
