import React from "react";
import { connect } from "react-redux";
import {
  FieldArray,
  getFormValues,
  InjectedFormProps,
  reduxForm,
  SubmissionError,
} from "redux-form";
import _ from "lodash";
import renderPartsForm from "../../components/Part/PartsArray";
import validate from "../../model/form/part/validation";
import { hideErrorAlert, hideSuccessAlert } from "../../actions/genericActions";
import {
  intialPartObjectFactory,
  partObjectFactory,
} from "../../model/form/part/partObjectFactory";
import { writePartObject as writePartObjectAction } from "../../actions/part/partActions";
import SweetAlert from "../../utils/SweetAlert";
import Loader from "../../components/Loader";
import { AppDispatch, RootState } from "../../store";

type IFormData = any;

interface IProps {
  hideSuccessMessage: any;
  hideErrorMessage: any;
  module: any;
  writePartObject: any;
  object: any;
  showSuccess?: any;
  showError?: any;
  showLoading?: any;
  formValues?: any;
  redirectUrl: any;
}

interface IState {
  redirectFlag: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class PartsForm extends React.Component<IWrappedProps, IState> {
  constructor(props: IWrappedProps) {
    super(props);
    this.state = {
      redirectFlag: false,
    };

    this.onCloseSweetAlert = this.onCloseSweetAlert.bind(this);
    this.onCloseErrorAlert = this.onCloseErrorAlert.bind(this);
  }

  onCloseSweetAlert() {
    const { hideSuccessMessage } = this.props;
    hideSuccessMessage();
    this.setState({ redirectFlag: true });
  }

  onCloseErrorAlert() {
    const { hideErrorMessage } = this.props;
    hideErrorMessage();
  }

  onSubmit(values: any) {
    const { module, writePartObject, object } = this.props;
    const errors = validate(values);
    if (!_.isEmpty(errors)) {
      throw new SubmissionError(errors);
    }
    const newObject = partObjectFactory(values, object, module);
    writePartObject(newObject);
  }

  render() {
    const {
      valid,
      object,
      module,
      handleSubmit,
      anyTouched,
      error,
      showSuccess,
      showError,
      showLoading,
      formValues,
      redirectUrl,
    } = this.props;

    const { redirectFlag } = this.state;

    if (redirectFlag && !showSuccess) {
      window.location.href = redirectUrl;
      return <Loader />;
    }
    const location = object.location || object.factory;
    return (
      <div className="row" style={{ position: "relative" }}>
        <SweetAlert
          show={showSuccess}
          title="Saved"
          type="success"
          onConfirm={() => this.onCloseSweetAlert()}
          onClose={() => this.onCloseSweetAlert()}
        />
        <SweetAlert
          show={showError}
          title="Edition failed"
          type="warning"
          confirmButtonColor="#DD6B55"
          confirmButtonText="OK"
          text={(formValues && formValues.errorMessage) || ""}
          onConfirm={() => this.onCloseErrorAlert()}
          onClose={() => this.onCloseErrorAlert()}
        />
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
        {error && anyTouched && (
          <div className="alert alert-danger">{error}</div>
        )}
        <form onSubmit={handleSubmit(this.onSubmit.bind(this))}>
          <div className="row">
            <div className="col-sm-12">
              <div className="ibox float-e-margins">
                <div className="ibox-title">
                  <h5>Admin Parts</h5>
                </div>
                <div className="ibox-content">
                  <div className="row">
                    <div className="col-sm-12">
                      <FieldArray
                        name="parts"
                        component={renderPartsForm}
                        location={location}
                        module={module}
                      />
                    </div>
                  </div>
                  <div className="row">
                    <div className="col-sm-12 text-end">
                      <button
                        className={`btn btn-${
                          valid ? "info" : "danger"
                        } m-b-xl`}
                        disabled={!valid}
                        type="submit"
                      >
                        Save
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    );
  }
}

const formConfiguration = {
  form: "parts_form",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const mapStateToProps = (state: RootState, props: IProps) => {
  return {
    initialValues: intialPartObjectFactory(props.object, props.module),
    formValues: getFormValues("parts_form")(state),
    showLoading: state.part.showLoading,
    showSuccess: state.part.showSuccess,
    showError: state.part.showError,
    ...props,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    hideSuccessMessage: () => dispatch(hideSuccessAlert("PART")),
    hideErrorMessage: () => dispatch(hideErrorAlert("PART")),
    writePartObject: (object: any) =>
      dispatch(writePartObjectAction(object, "parts_form")),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(PartsForm));
