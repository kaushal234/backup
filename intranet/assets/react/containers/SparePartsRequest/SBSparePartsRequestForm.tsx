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
import SweetAlert from "../../utils/SweetAlert";
import Loader from "../../components/Loader";
import renderSparePartsRequestForm from "../../components/SparePartsRequest/SparePartsRequestArray";
import validate from "../../model/form/spare_parts_request/sbValidation";
import { serviceBulletinSparePartsRequestFactory } from "../../model/form/spare_parts_request/factory";
import { writeSBSparePartsRequest } from "../../actions/sparePartsRequest/sparePartsRequestActions";
import { hideErrorAlert, hideSuccessAlert } from "../../actions/genericActions";
import { AppDispatch, RootState } from "../../store";

type IFormData = any;

interface IProps {
  hideSuccessMessage: any;
  hideErrorMessage: any;
  writeSparePartsRequest: any;
  showLoading?: any;
  showError?: any;
  formValues?: any;
  sbId: any;
  submittedSPR?: any;
}

interface IState {
  redirectFlag: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class SBSparePartsRequestForm extends React.Component<IWrappedProps, IState> {
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
    const { writeSparePartsRequest } = this.props;
    const errors = validate(values);
    if (!_.isEmpty(errors)) {
      throw new SubmissionError(errors);
    }
    let i = 0;
    for (i; i < values.sparePartsRequests.length; i++) {
      writeSparePartsRequest(
        serviceBulletinSparePartsRequestFactory(values.sparePartsRequests[i]),
        i
      );
    }
  }

  render() {
    const {
      valid,
      handleSubmit,
      anyTouched,
      error,
      showLoading,
      showError,
      formValues,
      sbId,
      submittedSPR,
    } = this.props;

    const { redirectFlag } = this.state;

    if (redirectFlag) {
      window.location.href = `/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&id=${sbId}`;
      return <Loader />;
    }
    return (
      <div className="row" style={{ position: "relative" }}>
        <SweetAlert
          show={
            formValues && formValues.sparePartsRequests.length === submittedSPR
          }
          title="Saved"
          type="success"
          onConfirm={() => this.onCloseSweetAlert()}
          onClose={() => this.onCloseSweetAlert()}
        />
        <SweetAlert
          show={showError}
          title="Creation failed"
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
        <form
          onSubmit={handleSubmit(this.onSubmit.bind(this))}
          style={{ opacity: showLoading ? 0.1 : 1 }}
        >
          <div className="row">
            <div className="col-xs-12">
              <div className="ibox float-e-margins">
                <div className="ibox-title">
                  <h5>Create SPR</h5>
                </div>
                <div className="ibox-content">
                  <FieldArray
                    name="sparePartsRequests"
                    component={renderSparePartsRequestForm}
                  />
                  <button
                    className={`btn btn-${
                      valid ? "info" : "danger"
                    } m-b-xl mt-2`}
                    disabled={!valid}
                    type="submit"
                  >
                    Save
                  </button>
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
  form: "sb_spare_parts_request_form",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const mapStateToProps = (state: RootState, props: IProps) => {
  return {
    initialValues: {
      sparePartsRequests: state.spr.sparePartsRequests,
    },
    formValues: getFormValues("sb_spare_parts_request_form")(state),
    showLoading: state.spr.showLoading,
    showSuccess: state.spr.showSuccess,
    showError: state.spr.showError,
    submittedSPR: state.spr.submittedSPR,
    ...props,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    writeSparePartsRequest: (sparePartsRequest: any, index: any) =>
      dispatch(
        writeSBSparePartsRequest(
          sparePartsRequest,
          "sb_spare_parts_request_form",
          index
        )
      ),
    hideSuccessMessage: () => dispatch(hideSuccessAlert("SPR")),
    hideErrorMessage: () => dispatch(hideErrorAlert("SPR")),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(SBSparePartsRequestForm));
