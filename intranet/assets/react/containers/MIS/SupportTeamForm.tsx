import React, { useEffect, useState } from "react";
import { connect } from "react-redux";
import { Button, Row, Col } from "react-bootstrap";
import { InjectedFormProps, reduxForm, SubmissionError } from "redux-form";
import Translator from "bazinga-translator";
import _ from "lodash";
import Swal from "sweetalert2";
import validate from "../../model/form/mis/support_team/validation";
import { hideErrorAlert, hideSuccessAlert } from "../../actions/genericActions";
import Loader from "../../components/Loader";
import { AppDispatch, RootState } from "../../store";
import {
  supportTeamFactory,
  supportTeamFactoryForm,
} from "../../model/form/mis/support_team/factory";
import {
  addSupportTeam,
  updateSupportTeam,
} from "../../actions/mis/supportTeamActions";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";

type IFormData = any;

interface IProps {
  formType: "add" | "edit";
  // eslint-disable-next-line react/no-unused-prop-types
  writeSupportTeam: any;
  showError: any;
  showLoading: any;
  showSuccess: any;
  errorMessage: any;
  hideSuccessMessage: any;
  hideErrorMessage: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

function SupportTeamForm({
  formType,
  writeSupportTeam,
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
      writeSupportTeam(updateSupportTeam(supportTeamFactory(values)));
    } else {
      writeSupportTeam(addSupportTeam(supportTeamFactory(values)));
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
        title: formType === "add" ? "Creation failed." : "Edition failed.",
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "OK",
      }).then(hideErrorMessage);
    }
  }, [showSuccess, showError]);

  if (redirectFlag) {
    window.location.href = `/en/private/mis/support-teams`;
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
                  ? Translator.trans("support_team.edit.title")
                  : Translator.trans("support_team.add.title")}
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
                      label={Translator.trans("support_team.fields.name")}
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
  form: "supplier_team_form",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const mapStateToProps = (state: RootState, props: IProps) => {
  const { mis } = state;
  let initialValues = {};

  if (props.formType === "edit") {
    initialValues = supportTeamFactoryForm(mis.details);
  }

  return {
    initialValues,
    formType: props.formType,
    showError: state.mis.showError,
    showLoading: state.mis.showLoading,
    showSuccess: state.mis.showSuccess,
    errorMessage: state.mis.errorMessage,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    writeSupportTeam: (action: any) => dispatch(action),
    hideSuccessMessage: () => dispatch(hideSuccessAlert("MIS")),
    hideErrorMessage: () => dispatch(hideErrorAlert("MIS")),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(SupportTeamForm));
