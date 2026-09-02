import React from "react";
import { connect } from "react-redux";
import {
  Field,
  FieldArray,
  getFormValues,
  InjectedFormProps,
  reduxForm,
  SubmissionError,
} from "redux-form";
import Translator from "bazinga-translator";
import _ from "lodash";
import renderPartsForm from "../../components/Part/PartsArray";
import { fetchDeliveryAddresses as fetchDeliveryAddressesAction } from "../../actions/sparePartsRequest/deliveryAddressesActions";
import DeliveryAddressesSelect from "../../components/Forms/SparePartsRequest/DeliveryAddressesSelect";
import {
  renderReactVerticalSelect,
  renderVerticalSelect,
} from "../../components/Forms/Elements";
import { fetchAirport as fetchAirportAction } from "../../actions/apc/airportsActions";
import { SPR_FETCH_AIRPORT } from "../../constants";
import validate from "../../model/form/spare_parts_request/validation";
import { tocSparePartsRequestFactory } from "../../model/form/spare_parts_request/factory";
import { writeTocSparePartsRequest } from "../../actions/sparePartsRequest/sparePartsRequestActions";
import LocationSelect from "../../components/Forms/LocationsSelect";
import SweetAlert from "../../utils/SweetAlert";
import Loader from "../../components/Loader";
import { hideErrorAlert, hideSuccessAlert } from "../../actions/genericActions";
import SparePartsRequestNewAddress from "../../components/SparePartsRequest/SparePartsRequestNewAddress";
import { initialPartsObjectFactory } from "../../model/form/part/partObjectFactory";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";

type IFormData = any;

interface IProps {
  airport: any;
  fetchDeliveryAddresses: any;
  customers: any;
  fetchAirport: any;
  newSparePartsRequest: any;
  hideSuccessMessage: any;
  hideErrorMessage: any;
  writeSparePartsRequest: any;
  sparePartsRequest: any;
  location: any;
  showLoading?: any;
  showSuccess?: any;
  showError?: any;
  formValues?: any;
}

interface IState {
  redirectFlag: any;
  sph: any;
  newAddress: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class TOCSparePartsRequestForm extends React.Component<IWrappedProps, IState> {
  constructor(props: IWrappedProps) {
    super(props);
    this.state = {
      newAddress: false,
      redirectFlag: false,
      sph: true,
    };

    this.onCloseSweetAlert = this.onCloseSweetAlert.bind(this);
    this.onCloseErrorAlert = this.onCloseErrorAlert.bind(this);
  }

  componentDidMount() {
    const {
      airport,
      fetchDeliveryAddresses,
      customers,
      fetchAirport,
      newSparePartsRequest,
    } = this.props;
    if (airport) {
      fetchDeliveryAddresses(airport, customers);
      fetchAirport(airport);
    }

    if (!newSparePartsRequest.sph) {
      this.setState({ sph: false });
    }
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
    const { newSparePartsRequest, writeSparePartsRequest, sparePartsRequest } =
      this.props;
    const errors = validate(values);
    if (!_.isEmpty(errors)) {
      throw new SubmissionError(errors);
    }
    const finalSparePartsRequest = tocSparePartsRequestFactory(
      values,
      sparePartsRequest,
      newSparePartsRequest
    );
    writeSparePartsRequest(finalSparePartsRequest);
  }

  render() {
    const {
      location,
      valid,
      handleSubmit,
      anyTouched,
      error,
      newSparePartsRequest,
      showLoading,
      showSuccess,
      showError,
      formValues,
    } = this.props;
    const { redirectFlag, sph, newAddress } = this.state;

    if (redirectFlag && !showSuccess) {
      window.location.href = `/en/private/service/technician-on-calls/${newSparePartsRequest.tocId}/show`;
      return <Loader />;
    }
    const choices: any = {
      false: Translator.trans(
        "spare_parts_request.fields.existing_delivery_address"
      ),
      true: Translator.trans(
        "spare_parts_request.fields.submit_new_delivery_address"
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
        <form onSubmit={handleSubmit(this.onSubmit.bind(this))}>
          <div className="row">
            <div className="col-xs-12">
              <div className="ibox float-e-margins">
                <div className="ibox-title">
                  <h5>Add Parts</h5>
                </div>
                <div className="ibox-content">
                  <div className="row">
                    <div className="col-xs-12">
                      <FieldArray
                        name="parts"
                        component={renderPartsForm}
                        location={location}
                        module="SPR"
                      />
                      {!sph && (
                        <div className="row">
                          <div className="col-md-3">
                            <LocationSelect
                              component={renderReactVerticalSelect}
                              required
                              label="SPH"
                              name="sph"
                              placeholder="Select a SPH"
                              locationListName="sparePartsHubs"
                            />
                          </div>
                        </div>
                      )}
                      <div className="row">
                        <div className="col-md-5">
                          <p className="small" style={{ color: "red" }}>
                            <strong>
                              {Translator.trans(
                                "spare_parts_request.errors.delivery_address"
                              )}
                            </strong>
                          </p>
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
                        <SparePartsRequestNewAddress form="spare_parts_request_form" />
                      )}
                      <GenericFormComponent
                        name="deliveryNotes"
                        type="Field"
                        isTextArea
                        label={Translator.trans(
                          "spare_parts_request.fields.delivery_notes"
                        )}
                      />
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
  form: "spare_parts_request_form",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const mapStateToProps = (state: RootState, props: IProps) => {
  return {
    initialValues: {
      parts: initialPartsObjectFactory(state.part.technicianOnCallParts),
    },
    formValues: getFormValues("spare_parts_request_form")(state),
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
          "spare_parts_request_form",
          SPR_FETCH_AIRPORT
        )
      ),
    writeSparePartsRequest: (sparePartsRequest: any) =>
      dispatch(
        writeTocSparePartsRequest(sparePartsRequest, "spare_parts_request_form")
      ),
    hideSuccessMessage: () => dispatch(hideSuccessAlert("SPR")),
    hideErrorMessage: () => dispatch(hideErrorAlert("SPR")),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(TOCSparePartsRequestForm));
