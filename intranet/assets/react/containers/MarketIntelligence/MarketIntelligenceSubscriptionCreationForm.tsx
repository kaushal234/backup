import React from "react";
import { connect } from "react-redux";
import { InjectedFormProps, reduxForm, SubmissionError } from "redux-form";
import Translator from "bazinga-translator";
import _ from "lodash";
import CompetitorAsyncSelect from "../../components/Forms/CompetitorAsyncSelect";
import { renderReactVerticalSelect } from "../../components/Forms/Elements";
import ProductTypeSelect from "../../components/Forms/ProductTypeSelect";
import CustomersSelect from "../../components/Forms/CustomersSelect";
import SweetAlert from "../../utils/SweetAlert";
import Loader from "../../components/Loader";
import { hideSuccessAlert } from "../../actions/genericActions";
import { marketIntelligenceSubscriptionFactory } from "../../model/form/market_intelligence/subscriptionFactory";
import { writeMarketIntelligenceSubscription as writeMarketIntelligenceSubscriptionAction } from "../../actions/marketIntelligence/marketIntelligenceSubscriptionsActions";
import validate from "../../model/form/market_intelligence/subscriptionValidation";
import MarketIntelligenceTypesSelect from "../../components/Forms/MarketIntelligenceTypesSelect";
import BusinessPartnersSelect from "../../components/Forms/BusinessPartnersSelect";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";

type IFormData = any;

interface IProps {
  hideSuccessMessage: any;
  writeMarketIntelligenceSubscription: any;
  showSuccess: any;
  showLoading: any;
}

interface IState {
  redirectFlag: any;
  disabled: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class MarketIntelligenceSubsciptionCreationForm extends React.Component<
  IWrappedProps,
  IState
> {
  constructor(props: IWrappedProps) {
    super(props);
    this.state = {
      disabled: false,
      redirectFlag: false,
    };
  }

  onCloseSweetAlert() {
    const { hideSuccessMessage } = this.props;
    hideSuccessMessage();
    this.setState({ redirectFlag: true });
  }

  onSubmit(values: any) {
    const { writeMarketIntelligenceSubscription } = this.props;
    values = { ...values, submit: true };
    const errors = validate(values);
    if (!_.isEmpty(errors)) {
      throw new SubmissionError(errors);
    }

    const marketIntelligenceSubscription =
      marketIntelligenceSubscriptionFactory(values);
    writeMarketIntelligenceSubscription(marketIntelligenceSubscription);
  }

  render() {
    const {
      submitting,
      handleSubmit,
      showSuccess,
      showLoading,
      valid,
      error,
      anyTouched,
    } = this.props;

    const { redirectFlag, disabled } = this.state;

    if (redirectFlag && !showSuccess) {
      window.location.href =
        "/en/private/sales/market-intelligence-subscriptions";
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
        <div className="row">
          {error && anyTouched && (
            <div className="alert alert-danger">{error}</div>
          )}
          <form
            onSubmit={handleSubmit(this.onSubmit.bind(this))}
            style={{ opacity: submitting ? 0.3 : 1 }}
          >
            <div className="row">
              <div className="col-md-6">
                <div className="ibox float-e-margins">
                  <div className="ibox-title">
                    <h5>
                      {Translator.trans("subscriptions.add_subscription")}
                    </h5>
                  </div>
                  <div className="ibox-content">
                    <div
                      style={{
                        opacity: disabled ? 0.3 : 1,
                        pointerEvents: disabled ? "none" : "auto",
                      }}
                    >
                      <h3 style={{ color: "red" }}>
                        {Translator.trans("subscriptions.add_text")}
                      </h3>
                      <MarketIntelligenceTypesSelect
                        required={false}
                        disabled={disabled}
                      />
                      <ProductTypeSelect
                        name="productType"
                        isDisabled={disabled}
                        label={Translator.trans("catalogue.type.product_type")}
                        placeholder="Select a Product Type"
                        productTypeList="productTypes"
                      />
                      <CompetitorAsyncSelect
                        name="competitor"
                        disabled={disabled}
                        label={Translator.trans("competitors.name")}
                        placeholder="Select a Competitor"
                      />
                      <CustomersSelect
                        required={false}
                        name="customer"
                        disabled={disabled}
                        label={Translator.trans("demo.fields.customer")}
                        placeholder="Select an eCustomer"
                      />
                      <BusinessPartnersSelect
                        required={false}
                        name="supplier"
                        filter="supplier"
                        disabled={disabled}
                        label={Translator.trans("finance.approver.supplier")}
                        component={renderReactVerticalSelect}
                      />
                    </div>
                    <GenericFormComponent
                      type="Checkbox"
                      name="all"
                      onChange={(event, value) => {
                        this.setState({ disabled: value });
                      }}
                      label={Translator.trans("subscriptions.select_all")}
                      checkboxFirst
                      fitContent
                    />
                    <div className="row">
                      <div className="col-xs-12 text-end">
                        <button
                          className={`btn btn-${
                            valid ? "info" : "danger"
                          } m-b-xl`}
                          type="submit"
                          disabled={submitting || !valid}
                        >
                          <i className="fa fa-fw fa-save" />
                          &nbsp;
                          {Translator.trans("button.save")}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    );
  }
}

const formConfiguration = {
  form: "market_intelligence_subscription_creation",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const mapStateToProps = (state: RootState) => {
  return {
    showLoading: state.marketIntelligenceSubscription.showLoading,
    showSuccess: state.marketIntelligenceSubscription.showSuccess,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    writeMarketIntelligenceSubscription: (
      marketIntelligenceSubscription: any
    ) =>
      dispatch(
        writeMarketIntelligenceSubscriptionAction(
          marketIntelligenceSubscription,
          formConfiguration.form
        )
      ),
    hideSuccessMessage: () => dispatch(hideSuccessAlert("MIM_NOT")),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(
  reduxForm<IFormData, IProps>(formConfiguration)(
    MarketIntelligenceSubsciptionCreationForm
  )
);
