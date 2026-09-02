import React, { useEffect, useState } from "react";
import { connect } from "react-redux";
import { Button, Row, Col } from "react-bootstrap";
import { InjectedFormProps, reduxForm, SubmissionError } from "redux-form";
import Translator from "bazinga-translator";
import _ from "lodash";
import Swal from "sweetalert2";
import CountrySelect from "../../../components/Forms/CountriesSelect";
import validate from "../../../model/form/human_resources/event/validation";
import {
  addEvent,
  updateEvent,
} from "../../../actions/humanResources/event/eventActions";
import {
  eventFactory,
  eventFactoryForm,
} from "../../../model/form/human_resources/event/factory";
import {
  hideErrorAlert,
  hideSuccessAlert,
} from "../../../actions/genericActions";
import Loader from "../../../components/Loader";
import { AppDispatch, RootState } from "../../../store";
import GenericFormComponent from "../../../components/GenericFormComponent/GenericFormComponent";

type IFormData = any;

interface IProps {
  formType: "add" | "edit";
  // eslint-disable-next-line react/no-unused-prop-types
  eventValues: any;
  writeEvent: any;
  showError: any;
  showLoading: any;
  showSuccess: any;
  errorMessage: any;
  hideSuccessMessage: any;
  hideErrorMessage: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

function EventForm({
  formType,
  writeEvent,
  handleSubmit,
  valid,
  submitting,
  showError,
  showLoading,
  showSuccess,
  errorMessage,
  hideSuccessMessage,
  hideErrorMessage,
}: IWrappedProps) {
  const [redirectFlag, setRedirectFlag] = useState(false);

  const onSubmit = (values: any) => {
    const errors = validate(values);
    if (!_.isEmpty(errors)) {
      throw new SubmissionError(errors);
    }

    if (formType === "edit") {
      writeEvent(updateEvent(eventFactory(values)));
    } else {
      writeEvent(addEvent(eventFactory(values)));
    }
  };

  useEffect(() => {
    if (showSuccess) {
      Swal.fire({
        icon: "success",
        title: "Saved",
        confirmButtonText: "OK",
      }).then(hideSuccessMessage);
      setRedirectFlag(true);
    }
    if (showError) {
      Swal.fire({
        icon: "warning",
        text: errorMessage || "",
        title: "Creation failed",
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "OK",
      }).then(hideErrorMessage);
    }
  }, [showSuccess, showError]);

  if (redirectFlag) {
    window.location.href = `/en/private/human-resources/events`;
    return <Loader />;
  }

  return (
    <div style={{ position: "relative" }}>
      <Row>
        {showLoading && (
          <Loader
            style={{
              position: "absolute",
              top: 0,
              bottom: 0,
              left: 0,
              right: 0,
              zIndex: 20,
            }}
          />
        )}
        <Col className="col-md-12 col-lg-6">
          <div className="card">
            <div className="card-header">
              <h3>
                {formType === "edit"
                  ? Translator.trans("events.edit.title")
                  : Translator.trans("events.add.title")}
              </h3>
            </div>
            <div className="card-body">
              <form
                onSubmit={handleSubmit(onSubmit)}
                style={{
                  opacity: submitting ? 0.3 : 1,
                  pointerEvents: submitting ? "none" : "auto",
                }}
              >
                <GenericFormComponent type="Field" name="id" hidden />
                <Row>
                  <Col className="col-md-10">
                    <GenericFormComponent
                      type="Field"
                      name="name"
                      label={Translator.trans("events.fields.name")}
                      required
                    />
                  </Col>
                  <Col className="col-md-2">
                    <GenericFormComponent
                      type="Switch"
                      label={Translator.trans("events.fields.day_off")}
                      name="dayOff"
                      required
                      isVertical
                      centered
                    />
                  </Col>
                </Row>
                <Row>
                  <Col className="col-md-6">
                    <CountrySelect
                      name="country"
                      label={Translator.trans("events.fields.country")}
                      required
                    />
                  </Col>
                  <Col className="col-md-6">
                    <GenericFormComponent
                      type="Field"
                      name="state"
                      label={Translator.trans("events.fields.state")}
                    />
                  </Col>
                </Row>
                <Row>
                  <Col className="col-md-6">
                    <GenericFormComponent
                      type="DatePicker"
                      label={Translator.trans("events.fields.started_at")}
                      name="startedAt"
                      dateformat="Y-M-D"
                      required
                    />
                  </Col>
                  <Col className="col-md-6">
                    <GenericFormComponent
                      type="DatePicker"
                      label={Translator.trans("events.fields.ended_at")}
                      name="endedAt"
                      dateformat="Y-M-D"
                      required
                    />
                  </Col>
                </Row>
                <Row className="mt-3">
                  <Col className="col-xs-12 text-end">
                    <Button
                      className={`btn btn-${valid ? "info" : "danger"} m-b-xl`}
                      type="submit"
                      disabled={submitting || !valid}
                    >
                      <i className="fa fa-fw fa-save" />
                      &nbsp;
                      {Translator.trans("button.save")}
                    </Button>
                  </Col>
                </Row>
              </form>
            </div>
          </div>
        </Col>
      </Row>
    </div>
  );
}

const formConfiguration = {
  form: "event_form",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const mapStateToProps = (state: RootState, props: IProps) => {
  let initialValues: any = {
    dayOff: false,
  };

  if (props.formType === "edit") {
    initialValues = eventFactoryForm(props.eventValues);
  }

  return {
    initialValues,
    formType: props.formType,
    eventValues: props.eventValues,
    showError: state.event.showError,
    showLoading: state.event.showLoading,
    showSuccess: state.event.showSuccess,
    errorMessage: state.event.errorMessage,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    writeEvent: (action: any) => dispatch(action),
    hideSuccessMessage: () => dispatch(hideSuccessAlert("EVENT")),
    hideErrorMessage: () => dispatch(hideErrorAlert("EVENT")),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(EventForm));
