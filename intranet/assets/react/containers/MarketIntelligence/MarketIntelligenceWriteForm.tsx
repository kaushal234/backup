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
import CompetitorAsyncSelect from "../../components/Forms/CompetitorAsyncSelect";
import {
  renderReactVerticalSelect,
  renderVerticalSelect,
} from "../../components/Forms/Elements";
import ProductTypeSelect from "../../components/Forms/ProductTypeSelect";
import validate from "../../model/form/market_intelligence/validation";
import CustomersSelect from "../../components/Forms/CustomersSelect";
import {
  marketIntelligenceEditFormFactory,
  marketIntelligenceFactory,
} from "../../model/form/market_intelligence/factory";
import { writeMarketIntelligence as writeMarketIntelligenceAction } from "../../actions/marketIntelligence/marketIntelligencesActions";
import SweetAlert from "../../utils/SweetAlert";
import Loader from "../../components/Loader";
import { hideErrorAlert, hideSuccessAlert } from "../../actions/genericActions";
import { MarketIntelligenceFileUploader } from "../../components/MarketIntelligence/MarketIntelligenceFileUploader";
import { writeCompetitorPricing as writeCompetitorPricingAction } from "../../actions/competitorPricing/competitorPricingActions";
import TermsOfDeliverySelect from "../../components/Forms/TermsOfDeliverySelect";
import CurrenciesSelect from "../../components/Forms/CurrenciesSelect";
import { competitorPricingFactory } from "../../model/form/competitor_pricing/factory";
import { fetchCurrencies as fetchCurrenciesAction } from "../../actions/currency/currenciesActions";
import { fetchTermsOfDeliveries as fetchTermsOfDeliveriesAction } from "../../actions/incoterm/termsOfDeliveryActions";
import MarketIntelligenceTypesSelect from "../../components/Forms/MarketIntelligenceTypesSelect";
import BusinessPartnersSelect from "../../components/Forms/BusinessPartnersSelect";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";

type IFormData = any;

interface IProps {
  fetchCurrencies: any;
  fetchTermsOfDeliveries: any;
  hideSuccessMessage: any;
  hideErrorMessage: any;
  writeCompetitorPricing: any;
  writeMarketIntelligence: any;
  showSuccess: any;
  showLoading: any;
  showFiles: any;
  clearFields: any;
  formValues: any;
  isGrantedCreate: any;
  divisions: any;
  positionLevels: any;
  formType: any;
  showError: any;
  match: any;
}

interface IState {
  showCompetitorPricingFields: any;
  cprSaved: any;
  showCompetitorPricingAddButton: any;
  positionLevelModified: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class MarketIntelligenceWriteForm extends React.Component<
  IWrappedProps,
  IState
> {
  constructor(props: IWrappedProps) {
    super(props);
    this.state = {
      showCompetitorPricingFields: false,
      showCompetitorPricingAddButton: false,
      cprSaved: false,
      positionLevelModified: false,
    };
  }

  componentDidUpdate() {
    const { fetchCurrencies, fetchTermsOfDeliveries } = this.props;
    const { showCompetitorPricingFields } = this.state;
    if (showCompetitorPricingFields) {
      fetchCurrencies();
      fetchTermsOfDeliveries();
    }
  }

  onCloseSweetAlert() {
    const { hideSuccessMessage } = this.props;
    hideSuccessMessage();
  }

  onCloseErrorAlert() {
    const { hideErrorMessage } = this.props;
    hideErrorMessage();
  }

  onSubmit(values: any) {
    const { formType, writeCompetitorPricing } = this.props;
    values = { ...values, submit: true, formType };
    const errors = validate(values);
    if (Object.keys(errors).length !== 0) {
      throw new SubmissionError(errors);
    }
    if (values.competitorPricing) {
      const competitorPricing = competitorPricingFactory(
        values.competitorPricing
      );
      writeCompetitorPricing(competitorPricing);
    }

    this.setState({ cprSaved: true }, () => {
      this.saveMarketIntelligence(values);
    });
  }

  saveMarketIntelligence(values: any) {
    const { writeMarketIntelligence } = this.props;
    const { cprSaved } = this.state;
    if (cprSaved) {
      const marketIntelligence = marketIntelligenceFactory(values);
      writeMarketIntelligence(marketIntelligence);
    }
  }

  render() {
    Translator.trans("market_intelligence.add.info_competitor_pricing");
    const {
      submitting,
      valid,
      handleSubmit,
      showSuccess,
      showLoading,
      showFiles,
      clearFields,
      error,
      formValues,
      isGrantedCreate,
      divisions,
      positionLevels,
      formType,
      initialValues,
      showError,
    } = this.props;

    const {
      cprSaved,
      showCompetitorPricingAddButton,
      positionLevelModified,
      showCompetitorPricingFields,
    } = this.state;

    return (
      <div style={{ position: "relative" }}>
        <SweetAlert
          show={showSuccess && cprSaved}
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
        <SweetAlert
          show={showError}
          title="Creation failed"
          type="warning"
          confirmButtonColor="#DD6B55"
          confirmButtonText="OK"
          text={
            (formValues &&
              formValues.competitorPricing &&
              formValues.competitorPricing.errorMessage) ||
            ""
          }
          onConfirm={() => this.onCloseErrorAlert()}
          onClose={() => this.onCloseErrorAlert()}
        />
        <div className="row">
          <form
            onSubmit={handleSubmit(this.onSubmit.bind(this))}
            style={{
              opacity: submitting || (!showSuccess && showFiles) ? 0.3 : 1,
              pointerEvents:
                submitting || (!showSuccess && showFiles) ? "none" : "auto",
            }}
          >
            {error && <div className="alert alert-danger">{error}</div>}
            <div className="row">
              <div className="col-md-6">
                <div className="ibox float-e-margins">
                  <div className="ibox-title">
                    <h5>{Translator.trans("market_intelligence.add.title")}</h5>
                  </div>
                  <div className="ibox-content">
                    <GenericFormComponent type="Field" name="id" hidden />
                    <MarketIntelligenceTypesSelect
                      required
                      onChange={(event: any, type: any) => {
                        if (
                          type.label === "Pricing info" &&
                          isGrantedCreate &&
                          formType === "add"
                        ) {
                          this.setState({
                            showCompetitorPricingAddButton: true,
                          });
                        } else {
                          this.setState({
                            showCompetitorPricingAddButton: false,
                            showCompetitorPricingFields: false,
                          });
                        }
                      }}
                    />
                    {formType === "add" && showCompetitorPricingAddButton && (
                      <div>
                        <p style={{ color: "red" }}>
                          {Translator.trans(
                            "market_intelligence.add.info_competitor_pricing"
                          )}
                        </p>
                        <button
                          className="btn btn-info"
                          type="button"
                          onClick={() =>
                            this.setState({
                              showCompetitorPricingFields: true,
                              showCompetitorPricingAddButton: false,
                            })
                          }
                        >
                          <i className="fa fa-fw fa-plus" />
                          &nbsp;
                          {Translator.trans("competitor_pricings.add.title")}
                        </button>
                      </div>
                    )}
                    <GenericFormComponent
                      type="Field"
                      name="shortDescription"
                      label={Translator.trans(
                        "market_intelligence.fields.shortDescription"
                      )}
                      required
                    />
                    <ProductTypeSelect
                      name="productTypes"
                      label={Translator.trans(
                        "market_intelligence.fields.productTypes"
                      )}
                      isMulti
                      placeholder="Select one/multiple Product Type(s)"
                      productTypeList="productTypes"
                    />
                    <CompetitorAsyncSelect
                      name="competitors"
                      label={Translator.trans(
                        "market_intelligence.fields.competitors"
                      )}
                      isMulti
                      placeholder="Type to search one/multiple Competitor(s)"
                    />
                    <CustomersSelect
                      required={false}
                      name="customers"
                      label={Translator.trans(
                        "market_intelligence.fields.customers"
                      )}
                      isMulti
                      placeholder="Type to search one/multiple eCustomers(s)"
                    />
                    <BusinessPartnersSelect
                      required={false}
                      name="suppliers"
                      filter="supplier"
                      component={renderReactVerticalSelect}
                      label={Translator.trans(
                        "market_intelligence.fields.suppliers"
                      )}
                      isMulti
                      placeholder="Type to search one/multiple Supplier(s)"
                    />
                    <GenericFormComponent
                      type="Field"
                      name="url"
                      label={Translator.trans("customers.fields.url")}
                    />
                    <GenericFormComponent
                      type="Field"
                      name="description"
                      label={Translator.trans(
                        "market_intelligence.fields.description"
                      )}
                      isTextArea
                      required
                    />
                    <div>
                      <label className="col-form-label">
                        <span style={{ color: "#ed5565" }}>*&nbsp;</span>
                        {Translator.trans(
                          "market_intelligence.fields.divisions"
                        )}
                      </label>
                      <div>
                        {divisions.map((division: any, i: number) => (
                          <div
                            className="form-check form-check-inline"
                            key={`division${i}`}
                          >
                            <Field
                              component="input"
                              name={`divisions.${division["@id"]}`}
                              id={`division${i}`}
                              type="checkbox"
                              value={division["@id"]}
                              onChange={() => {
                                // empty on purpose
                              }}
                            />
                            &nbsp;
                            <label htmlFor={`division${i}`}>
                              <p>{division.name} </p>
                            </label>
                          </div>
                        ))}
                      </div>
                    </div>
                    <div>
                      <label className="col-form-label">
                        {Translator.trans(
                          "market_intelligence.fields.position_levels"
                        )}
                      </label>
                      <div>
                        {Object.entries(positionLevels).map(
                          ([key, value]: any) => (
                            <div className="form-check" key={key}>
                              <Field
                                component="input"
                                name="positionLevels"
                                id={`positionLevel${key}`}
                                type="radio"
                                value={value}
                                {...(formType === "edition" &&
                                !positionLevelModified &&
                                initialValues.positionLevels === value
                                  ? { checked: true }
                                  : {})}
                                onChange={() =>
                                  this.setState({ positionLevelModified: true })
                                }
                              />
                              &nbsp;
                              <label htmlFor={`positionLevel${key}`}>
                                <p>{key}</p>
                              </label>
                            </div>
                          )
                        )}
                      </div>
                    </div>
                    <div className="row">
                      <div className="col-xs-12">
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
              {showCompetitorPricingFields && (
                <div className="col-md-6">
                  <div className="ibox float-e-margins">
                    <div className="ibox-title">
                      <button
                        className="btn btn-danger"
                        type="button"
                        onClick={() => {
                          this.setState({
                            showCompetitorPricingFields: false,
                            showCompetitorPricingAddButton: true,
                          });
                          clearFields(
                            "market_intelligence_write",
                            true,
                            true,
                            "competitorPricing"
                          );
                        }}
                      >
                        <i className="fa fa-fw fa-times" />
                      </button>
                    </div>
                    <div className="ibox-content">
                      <div>
                        <Field
                          name="competitorPricing.competitor"
                          component={renderVerticalSelect}
                          required
                          label={Translator.trans("competitors.name")}
                        >
                          <option value="" selected disabled />
                          {formValues.competitors &&
                            Object.values(formValues.competitors).map(
                              (competitor: any) => (
                                <option
                                  value={competitor.value}
                                  key={competitor.label}
                                >
                                  {competitor.label}
                                </option>
                              )
                            )}
                        </Field>
                        <GenericFormComponent
                          type="DatePicker"
                          name="competitorPricing.quotationDate"
                          label={Translator.trans(
                            "competitor_pricings.fields.quotation_date"
                          )}
                          required
                        />
                        <GenericFormComponent
                          type="Field"
                          name="competitorPricing.model"
                          label={Translator.trans(
                            "competitor_pricings.fields.model"
                          )}
                          required
                        />
                        <TermsOfDeliverySelect
                          name="competitorPricing.termsOfDelivery"
                          component={renderVerticalSelect}
                        />
                        <GenericFormComponent
                          type="Field"
                          name="competitorPricing.quantity"
                          required
                          label={Translator.trans("fields.quantity")}
                          allowFloatsOnly
                        />
                        <div className="row">
                          <div className="col-md-8">
                            <GenericFormComponent
                              type="Field"
                              name="competitorPricing.price"
                              required
                              label={Translator.trans(
                                "competitor_pricings.fields.total_price"
                              )}
                              allowFloatsOnly
                            />
                          </div>
                          <div className="col-md-4">
                            <CurrenciesSelect
                              name="competitorPricing.currency"
                              component={renderVerticalSelect}
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </form>
        </div>
        {showFiles && !showSuccess && formValues.id && (
          <div className="row">
            <div className="ibox float-e-margins">
              <div className="ibox-title">
                <h5>
                  {Translator.trans("market_intelligence.add_file.title")}
                </h5>
              </div>
              <div className="ibox-content">
                <MarketIntelligenceFileUploader id={formValues.id} />
                <div className="text-center" style={{ marginTop: "5px" }}>
                  <a
                    href={`/en/private/sales/market-intelligences/${formValues.id}/show`}
                    className="btn btn-info m-b-xl"
                    type="submit"
                  >
                    &nbsp;{Translator.trans("button.finish")}
                  </a>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    );
  }
}

const formConfiguration = {
  form: "market_intelligence_write",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const mapStateToProps = (state: RootState, props: IProps) => {
  const { marketIntelligence } = state;
  const { formType, divisions, positionLevels } = props;
  let initialValues = {};

  if (props.formType === "edition" && _.has(marketIntelligence, "details.id")) {
    initialValues = marketIntelligenceEditFormFactory(
      marketIntelligence.details
    );
  }

  return {
    initialValues,
    formType,
    divisions,
    positionLevels,
    formValues: getFormValues("market_intelligence_write")(state),
    showLoading: state.marketIntelligence.showLoading,
    showSuccess: state.marketIntelligence.showSuccess,
    showError: state.competitorPricing.showError,
    showFiles: state.marketIntelligence.showFiles,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    writeMarketIntelligence: (marketIntelligence: any) =>
      dispatch(
        writeMarketIntelligenceAction(
          marketIntelligence,
          formConfiguration.form
        )
      ),
    writeCompetitorPricing: (competitorPricing: any) =>
      dispatch(
        writeCompetitorPricingAction(competitorPricing, formConfiguration.form)
      ),
    hideSuccessMessage: () => dispatch(hideSuccessAlert("MIM")),
    hideErrorMessage: () => dispatch(hideErrorAlert("CPR")),
    fetchCurrencies: () => dispatch(fetchCurrenciesAction()),
    fetchTermsOfDeliveries: () => dispatch(fetchTermsOfDeliveriesAction()),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(MarketIntelligenceWriteForm));
