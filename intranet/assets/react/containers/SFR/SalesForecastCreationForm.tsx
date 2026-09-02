import React from "react";
import { connect } from "react-redux";
import {
  Field,
  FieldArray,
  reduxForm,
  getFormValues,
  SubmissionError,
  InjectedFormProps,
} from "redux-form";
import _ from "lodash";
import Col from "react-bootstrap/Col";
import Row from "react-bootstrap/Row";
import validate from "../../model/form/sfr/validationMaster";
import {
  renderReactVerticalSelect,
  renderVerticalSelect,
} from "../../components/Forms/Elements";
import PeopleSelect from "../../components/Forms/PeopleSelect";
import LocationSelect from "../../components/Forms/LocationsSelect";
import CustomersSelect from "../../components/Forms/CustomersSelect";
import CountrySelect from "../../components/Forms/CountriesSelect";
import renderSalesForecastForm from "../../components/SFR/SalesForecastsCreationArray";
import {
  getValorizationForSalesForecast,
  writeMasterSalesForecast as writeMasterSalesForecastAction,
} from "../../actions/sfr/sfrActions";
import {
  getUserDetails,
  getUserMapping,
} from "../../selectors/user/userSelectors";
import { getLocationMapping } from "../../selectors/location/locationSelectors";
import { masterSalesForecastArrayFactory } from "../../model/form/sfr/factoryMaster";
import SweetAlert from "../../utils/SweetAlert";
import Loader from "../../components/Loader";
import { hideSuccessAlert } from "../../actions/genericActions";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";

type IFormData = any;

interface IProps {
  writeMasterSalesForecast: any;
  hideSuccessMessage: any;
  formValues: any;
  fetchValorization: any;
  showLoading: any;
  showSuccess: any;
}

interface IState {
  redirectFlag: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class SalesForecastCreationForm extends React.Component<IWrappedProps, IState> {
  constructor(props: IWrappedProps) {
    super(props);
    this.state = {
      redirectFlag: false,
    };

    this.onCloseSweetAlert = this.onCloseSweetAlert.bind(this);
  }

  onSubmit(values: any) {
    const { writeMasterSalesForecast } = this.props;
    values = { ...values, submit: true };
    const errors = validate(values);
    if (!_.isEmpty(errors)) {
      throw new SubmissionError(errors);
    }
    const masterSalesForecastArray = masterSalesForecastArrayFactory(values);
    let i = 0;
    const apiErrors = {};
    for (i; i < masterSalesForecastArray.length; i++) {
      const allSubmitted = i === masterSalesForecastArray.length - 1;
      writeMasterSalesForecast(
        masterSalesForecastArray[i],
        i,
        allSubmitted,
        apiErrors
      );
    }
  }

  onCloseSweetAlert() {
    const { hideSuccessMessage } = this.props;
    hideSuccessMessage();
    this.setState({ redirectFlag: true });
  }

  render() {
    const {
      handleSubmit,
      formValues,
      fetchValorization,
      error,
      anyTouched,
      valid,
      showLoading,
      showSuccess,
    } = this.props;

    const { redirectFlag } = this.state;

    const currency =
      formValues && formValues.sso ? formValues.sso.currency : "";
    const onFactoryProductChange = (index: any, propName: any) => {
      return (value: any) => {
        const salesForecast = formValues.salesForecasts[index];
        salesForecast[propName] = value;
        if (formValues.sso && salesForecast.factory && salesForecast.product) {
          fetchValorization(
            salesForecast.product.financeFamily.value,
            salesForecast.factory.value,
            formValues.sso.value,
            index
          );
        }
      };
    };

    const onSSOChange = (sso: any) => {
      formValues.salesForecasts.forEach((salesForecast: any) => {
        if (salesForecast.factory && salesForecast.product) {
          // dispatch actions fetch prices & margins
          fetchValorization(
            salesForecast.product.financeFamily.value,
            salesForecast.factory.value,
            sso.value
          );
        }
      });
    };

    if (redirectFlag && !showSuccess) {
      window.location.href = `/en/private/sales/sales-forecasts/dashboard`;
      return <Loader />;
    }

    return (
      <div style={{ position: "relative" }}>
        <SweetAlert
          show={showSuccess}
          title="Saved"
          type="success"
          onConfirm={() => this.onCloseSweetAlert()}
          onClose={() => this.onCloseSweetAlert()}
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
        <form
          onSubmit={handleSubmit(this.onSubmit.bind(this))}
          style={{ opacity: showLoading ? 0.1 : 1 }}
        >
          {error && anyTouched && (
            <div className="alert alert-danger">{error}</div>
          )}
          <div className="row">
            <div className="col-md-12 col-lg-9">
              <div className="ibox float-e-margins">
                <div className="ibox-title">
                  <h5>Submit SFR</h5>
                </div>
                <div className="ibox-content">
                  <Row>
                    <Col>
                      <Field
                        component={renderVerticalSelect}
                        name="status"
                        placeholder="Select a status"
                        label="Status"
                        required
                      >
                        <option value="selected disabled">
                          Select a status
                        </option>
                        {["BUDGET", "DELAYED", "IN_PROGRESS"].map((status) => (
                          <option value={status} key={status}>
                            {status}
                          </option>
                        ))}
                      </Field>
                    </Col>
                    <Col>
                      <PeopleSelect
                        component={renderReactVerticalSelect}
                        required
                        label="ASM"
                        name="asm"
                        placeholder="Select an ASM"
                        userListName="asms"
                      />
                    </Col>
                    <Col>
                      <LocationSelect
                        component={renderReactVerticalSelect}
                        onChange={onSSOChange}
                        label="SSO"
                        name="sso"
                        placeholder="Select a SSO"
                        locationListName="ssos"
                      />
                    </Col>
                  </Row>
                  <Row>
                    <Col>
                      <CustomersSelect
                        label="Buyer"
                        name="buyer"
                        placeholder="Search a Buyer"
                        adder
                        required
                        formName="sfr_create_form"
                        showHidden
                      />
                    </Col>
                    <Col>
                      <CustomersSelect
                        label="End User"
                        name="endUser"
                        placeholder="Search an End User"
                        adder
                        required={false}
                        formName="sfr_create_form"
                        showHidden
                      />
                    </Col>
                    <Col>
                      <CountrySelect required />
                    </Col>
                  </Row>
                  <Row>
                    <Col>
                      <CustomersSelect
                        label="Third Party"
                        name="thirdParty"
                        placeholder="Search a Third Party"
                        adder
                        formName="sfr_create_form"
                        required={false}
                        showHidden
                      />
                    </Col>
                  </Row>
                  <GenericFormComponent
                    name="synchronized"
                    label="Do you want these SFR to be linked together ?"
                    type="Checkbox"
                    fitContent
                    checkboxFirst
                  />
                  <div className="row">
                    <FieldArray
                      name="salesForecasts"
                      component={renderSalesForecastForm}
                      currency={currency}
                      onFactoryProductChange={onFactoryProductChange}
                    />
                  </div>
                  <div className="row">
                    <div className="col-xs-12 text-end">
                      <button
                        className={`btn btn-${
                          valid ? "info" : "danger"
                        } m-b-xl`}
                        disabled={!valid || showLoading}
                        type="submit"
                      >
                        Save
                      </button>
                    </div>
                  </div>
                  <div />
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
  form: "sfr_create_form",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const mapStateToProps = (props: RootState) => {
  const connectedUser = getUserDetails(props);
  const defaultASM =
    getUserMapping({ ...props, iri: connectedUser["@id"] } as never, "asms") ||
    null;
  const defaultLocation =
    getLocationMapping(
      { ...props, erp: parseInt(connectedUser.erp, 10) as any },
      "ssos"
    ) || null;
  return {
    initialValues: {
      asm: defaultASM,
      sso: defaultLocation,
      salesForecasts: [{}],
      synchronized: true,
    },
    formValues: getFormValues("sfr_create_form")(props),
    showLoading: props.sfr.showLoading,
    showSuccess: props.sfr.showSuccess,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchValorization: (
      financeFamilyIri: any,
      factoryIri: any,
      ssoIri: any,
      index: any
    ) =>
      dispatch(
        getValorizationForSalesForecast(
          financeFamilyIri,
          factoryIri,
          ssoIri,
          index
        )
      ),
    writeMasterSalesForecast: (
      masterSalesForecast: any,
      index: any,
      allSubmitted: any,
      apiErrors: any
    ) =>
      dispatch(
        writeMasterSalesForecastAction(
          masterSalesForecast,
          formConfiguration.form,
          index,
          allSubmitted,
          apiErrors
        )
      ),
    hideSuccessMessage: () => dispatch(hideSuccessAlert("SFR")),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(SalesForecastCreationForm));
