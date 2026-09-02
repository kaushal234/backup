import React from "react";
import { connect } from "react-redux";
import {
  Field,
  getFormValues,
  InjectedFormProps,
  reduxForm,
  SubmissionError,
} from "redux-form";
import Translator from "bazinga-translator";
import _ from "lodash";
import { fetchDeliveryAddresses as fetchDeliveryAddressesAction } from "../../actions/sparePartsRequest/deliveryAddressesActions";
import DeliveryAddressesSelect from "../../components/Forms/SparePartsRequest/DeliveryAddressesSelect";
import { renderVerticalSelect } from "../../components/Forms/Elements";
import { fetchAirport as fetchAirportAction } from "../../actions/apc/airportsActions";
import { SPR_FETCH_AIRPORT } from "../../constants";
import { fetchContact } from "../../actions/extranetUser/extranetUserActions";
import validate from "../../model/form/spare_parts_request/addressValidation";
import { editSparePartsRequestAddress as editSparePartsRequestAddressAction } from "../../actions/sparePartsRequest/sparePartsRequestActions";
import SweetAlert from "../../utils/SweetAlert";
import Loader from "../../components/Loader";
import { hideErrorAlert, hideSuccessAlert } from "../../actions/genericActions";
import {
  addressFactory,
  deliveryAddressFactory,
} from "../../model/form/spare_parts_request/factory";
import SparePartsRequestNewAddress from "../../components/SparePartsRequest/SparePartsRequestNewAddress";
import { AppDispatch, RootState } from "../../store";

type IFormData = any;

interface IProps {
  fetchDeliveryAddresses: any;
  sparePartsRequest: any;
  fetchAirport: any;
  hideSuccessMessage: any;
  hideErrorMessage: any;
  editSparePartsRequestAddress: any;
  showLoading?: any;
  showSuccess?: any;
  showError?: any;
  formValues?: any;
}

interface IState {
  redirectFlag: any;
  newAddress: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class SparePartsRequestAddressForm extends React.Component<
  IWrappedProps,
  IState
> {
  constructor(props: IWrappedProps) {
    super(props);
    this.state = {
      newAddress: true,
      redirectFlag: false,
    };

    this.onCloseSweetAlert = this.onCloseSweetAlert.bind(this);
    this.onCloseErrorAlert = this.onCloseErrorAlert.bind(this);
  }

  componentDidMount() {
    const { fetchDeliveryAddresses, sparePartsRequest, fetchAirport } =
      this.props;
    fetchDeliveryAddresses(sparePartsRequest.airport["@id"], [
      sparePartsRequest.customer["@id"],
    ]);
    fetchAirport(sparePartsRequest.airport["@id"]);
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
    const { editSparePartsRequestAddress, sparePartsRequest } = this.props;
    const errors = validate(values);
    if (!_.isEmpty(errors)) {
      throw new SubmissionError(errors);
    }
    const newSparePartsRequest = addressFactory(values, sparePartsRequest);
    editSparePartsRequestAddress(newSparePartsRequest);
  }

  render() {
    const {
      valid,
      anyTouched,
      error,
      sparePartsRequest,
      showLoading,
      handleSubmit,
      showSuccess,
      showError,
      formValues,
    } = this.props;

    const { redirectFlag, newAddress } = this.state;

    if (redirectFlag) {
      window.location.href = `/en/private/parts/spare-parts-requests/${sparePartsRequest.id}/show`;
      return <Loader />;
    }
    const choices: any = {
      true: Translator.trans(
        "spare_parts_request.fields.submit_new_delivery_address"
      ),
      false: Translator.trans(
        "spare_parts_request.fields.existing_delivery_address"
      ),
    };
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
                  <h5>Edit Address</h5>
                </div>
                <div className="ibox-content">
                  <div className="row">
                    <div className="col-xs-12">
                      <div className="row">
                        <div className="col-md-5">
                          <Field
                            component={renderVerticalSelect}
                            name="newAddress"
                            onChange={() =>
                              this.setState({
                                newAddress: !newAddress,
                              })
                            }
                          >
                            {Object.keys(choices).map((key) => (
                              <option value={key} key={key}>
                                {choices[key]}
                              </option>
                            ))}
                          </Field>
                        </div>
                      </div>
                      {!newAddress && <DeliveryAddressesSelect />}
                      {newAddress && (
                        <SparePartsRequestNewAddress form="spare_parts_request_address_form" />
                      )}
                    </div>
                  </div>
                  <div className="row">
                    <div className="col-xs-12 text-end">
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
  form: "spare_parts_request_address_form",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const mapStateToProps = (state: RootState, props: IProps) => {
  return {
    initialValues: deliveryAddressFactory(
      props.sparePartsRequest.deliveryAddress
    ),
    formValues: getFormValues("spare_parts_request_address_form")(state),
    showLoading: state.spr.showLoading,
    showSuccess: state.spr.showSuccess,
    showError: state.spr.showError,
    ...props,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchDeliveryAddresses: (airport: any, customers: any) =>
      dispatch(fetchDeliveryAddressesAction(airport, customers)),
    fetchAirport: (airport: any) =>
      dispatch(
        fetchAirportAction(
          airport,
          "spare_parts_request_address_form",
          SPR_FETCH_AIRPORT
        )
      ),
    fetchContact: (contact: any) =>
      dispatch(fetchContact(contact, "spare_parts_request_address_form")),
    editSparePartsRequestAddress: (sparePartsRequest: any) =>
      dispatch(
        editSparePartsRequestAddressAction(
          sparePartsRequest,
          "spare_parts_request_address_form"
        )
      ),
    hideSuccessMessage: () => dispatch(hideSuccessAlert("SPR")),
    hideErrorMessage: () => dispatch(hideErrorAlert("SPR")),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(
  reduxForm<IFormData, IProps>(formConfiguration)(SparePartsRequestAddressForm)
);
