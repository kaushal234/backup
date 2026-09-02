import React, { useEffect, useState } from "react";
import { connect } from "react-redux";
import { getFormValues, InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import _ from "lodash";
import Col from "react-bootstrap/Col";
import Row from "react-bootstrap/Row";
import Swal from "sweetalert2";
import { useSearchParams } from "react-router-dom";
import CustomersSelect from "../../components/Forms/CustomersSelect";
import { renderReactVerticalSelect } from "../../components/Forms/Elements";
import LocationSelect from "../../components/Forms/LocationsSelect";
import PeopleSelect from "../../components/Forms/PeopleSelect";
import { fetchSalesCustomer } from "../../actions/customer/customersActions";
import { SALES_CRT_FETCH_CUSTOMER } from "../../constants";
import ExtranetUserAsyncSelect from "../../components/Forms/ExtranetUserAsyncSelect";
import ExtranetUserGroupsSelect from "../../components/Forms/ExtranetUserGroupsSelect";
import validate from "../../model/form/customer_relationship_team/validation";
import {
  customerRelationshipTeamFactory,
  customerRelationshipTeamFactoryForm,
} from "../../model/form/customer_relationship_team/factory";
import { extranetUserAclFactory } from "../../model/form/extranetUserAcl/factory";
import { writeCustomerRelationshipTeam as writeCustomerRelationshipTeamAction } from "../../actions/customerRelationshipTeam/customerRelationshipTeamActions";
import { writeExtranetUserAcl as writeExtranetUserAclAction } from "../../actions/extranetUser/extranetUserAclActions";
import Loader from "../../components/Loader";
import { hideErrorAlert, hideSuccessAlert } from "../../actions/genericActions";
import { fetchExtranetUsersHierarchyList as fetchExtranetUsersHierarchyListAction } from "../../actions/extranetUser/extranetUserActions";
import { getSelectedCustomerBusinessPartnerCodesMapping } from "../../selectors/customer/customersSelector";
import CustomerBusinessPartnerCodeSelect from "../../components/Forms/CustomerBusinessPartnerCodeSelect";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";

type IFormData = any;

interface IProps {
  fetchExtranetUsersHierarchyList: any;
  fetchCustomer: any;
  businessPartnerCodesChoice: any;
  formValues: any;
  showCRTError: any;
  showExtranetUserAclError: any;
  showCRTLoading: any;
  showExtranetUserAclLoading: any;
  submittedExtranetUserGroup: any;
  crtCreated: any;
  showCRTSuccess: any;
  showExtranetUserAclSuccess: any;
  formType: any;
  errorMessage: any;
  writeCustomerRelationshipTeam: any;
  writeExtranetUserAcl: any;
  hideSuccessMessage: any;
  hideErrorMessage: any;
  extranetUserListEmpty: any;
  selectedCustomer: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

function CustomerRelationshipTeamForm({
  initialValues,
  fetchExtranetUsersHierarchyList,
  fetchCustomer,
  businessPartnerCodesChoice,
  valid,
  error,
  anyTouched,
  handleSubmit,
  formValues,
  showCRTError,
  showExtranetUserAclError,
  showCRTLoading,
  showExtranetUserAclLoading,
  submittedExtranetUserGroup,
  crtCreated,
  showCRTSuccess,
  showExtranetUserAclSuccess,
  formType,
  errorMessage,
  writeCustomerRelationshipTeam,
  writeExtranetUserAcl,
  hideSuccessMessage,
  hideErrorMessage,
  extranetUserListEmpty,
  change,
  selectedCustomer,
}: IWrappedProps) {
  const [searchParams] = useSearchParams();

  const [redirectFlag, setRedirectFlag] = useState(false);

  useEffect(() => {
    if (formType === "edition") {
      fetchCustomer(initialValues.customer.value);
    }

    if (formType === "duplicate") {
      fetchExtranetUsersHierarchyList(initialValues.customer.value);
    }
  }, []);

  useEffect(() => {
    const customerParam = searchParams.get("customer");
    if (formType === "creation" && customerParam) {
      fetchCustomer(customerParam);
      fetchExtranetUsersHierarchyList(customerParam);
    }
  }, []);

  useEffect(() => {
    if (formType === "creation" && selectedCustomer && !formValues.customer) {
      change("customer", {
        value: selectedCustomer["@id"],
        label: selectedCustomer.name,
      });
    }
  }, [selectedCustomer]);

  useEffect(() => {
    if (formType === "edition" && showCRTSuccess) {
      Swal.fire({
        icon: "success",
        title: "Saved",
        confirmButtonText: "OK",
      })
        .then(hideSuccessMessage("CRT"))
        .then(hideSuccessMessage("EXTRANET_USER"));
      setRedirectFlag(true);
    }
    if (
      formType !== "edition" &&
      showExtranetUserAclSuccess &&
      formValues &&
      formValues.extranetUserGroups &&
      formValues.extranetUserGroups.length === submittedExtranetUserGroup
    ) {
      Swal.fire({
        icon: "success",
        title: "Saved",
        confirmButtonText: "OK",
      })
        .then(hideSuccessMessage("CRT"))
        .then(hideSuccessMessage("EXTRANET_USER"));
      setRedirectFlag(true);
    }
  }, [
    showCRTSuccess,
    showExtranetUserAclSuccess,
    formValues,
    submittedExtranetUserGroup,
  ]);

  useEffect(() => {
    if (showCRTError || showExtranetUserAclError) {
      Swal.fire({
        icon: "warning",
        title: "Failed",
        text: errorMessage,
        confirmButtonText: "OK",
        confirmButtonColor: "#DD6B55",
      })
        .then(hideErrorMessage("CRT"))
        .then(hideErrorMessage("EXTRANET_USER"));
    }
  }, [showCRTError, showExtranetUserAclError, formType]);

  const onSubmit = (values: any) => {
    if (!crtCreated) {
      const customerRelationshipTeam = customerRelationshipTeamFactory(values);
      writeCustomerRelationshipTeam(customerRelationshipTeam);
    }

    if (crtCreated) {
      let i = 0;
      for (i; i < values.extranetUserGroups.length; i++) {
        writeExtranetUserAcl(extranetUserAclFactory(values, i), i);
      }
    }
  };

  if (redirectFlag) {
    window.location.href = `/en/private/sales/customer-relationship-teams/${formValues.id}/show`;
    return <Loader />;
  }

  return (
    <div style={{ position: "relative" }}>
      <div className="row">
        {(showCRTLoading || showExtranetUserAclLoading) && (
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
          onSubmit={handleSubmit(onSubmit)}
          style={{
            opacity: showCRTLoading || showExtranetUserAclLoading ? 0.1 : 1,
          }}
        >
          <div className="row">
            <div className="col-md-8">
              <div className="ibox float-e-margins">
                <div className="ibox-title">
                  <h5>
                    {Translator.trans(
                      "customer_relationship_team.forms.title.add"
                    )}
                  </h5>
                </div>
                <div className="ibox-content">
                  {!crtCreated && (
                    <div>
                      {formType !== "edition" && extranetUserListEmpty && (
                        <p style={{ color: "red" }}>
                          <strong>
                            {Translator.trans(
                              "customer_relationship_team.no_contacts"
                            )}
                          </strong>
                        </p>
                      )}
                      <GenericFormComponent type="Field" name="id" hidden />
                      <GenericFormComponent
                        type="Field"
                        name="formType"
                        hidden
                      />
                      <Row>
                        <Col md="12" lg="6">
                          <CustomersSelect
                            required
                            showActive
                            onChange={(value: any) => {
                              fetchCustomer(value.value);
                              fetchExtranetUsersHierarchyList(value.value);
                            }}
                          />
                        </Col>
                        {(formType === "edition" ||
                          extranetUserListEmpty === false) && (
                          <Col md="12" lg="6">
                            <CustomerBusinessPartnerCodeSelect
                              businessPartnerCodesChoice={
                                businessPartnerCodesChoice
                              }
                            />
                          </Col>
                        )}
                      </Row>
                      {(formType === "edition" ||
                        extranetUserListEmpty === false) && (
                        <div>
                          <hr />
                          <h3>
                            {Translator.trans(
                              "customer_relationship_team.title.locations_info"
                            )}
                          </h3>
                          <br />
                          <Row>
                            <Col md="12" lg="4">
                              <LocationSelect
                                name="erpLocation"
                                label={Translator.trans(
                                  "customer_relationship_team.fields.erp_location"
                                )}
                                locationListName="ssos"
                                component={renderReactVerticalSelect}
                                required
                              />
                            </Col>
                            <Col md="12" lg="4">
                              <LocationSelect
                                name="partsLocation"
                                label={Translator.trans(
                                  "customer_relationship_team.fields.parts_location"
                                )}
                                locationListName="sparePartsHubs"
                                component={renderReactVerticalSelect}
                                required
                              />
                            </Col>
                            <Col md="12" lg="4">
                              <LocationSelect
                                name="serviceLocation"
                                label={Translator.trans(
                                  "customer_relationship_team.fields.service_location"
                                )}
                                locationListName="serviceHubs"
                                component={renderReactVerticalSelect}
                                required
                              />
                            </Col>
                          </Row>
                          <hr />
                          <h3>
                            {Translator.trans(
                              "customer_relationship_team.title.contacts_info"
                            )}
                          </h3>
                          <br />
                          <Row>
                            <Col md="12" lg="4">
                              <PeopleSelect
                                name="salesRepresentative"
                                label={Translator.trans(
                                  "customer_relationship_team.fields.sales_representative"
                                )}
                                userListName="salesRepresentatives"
                                component={renderReactVerticalSelect}
                                required
                                isDisabled
                              />
                            </Col>
                            <Col md="12" lg="4">
                              <PeopleSelect
                                name="partsRepresentative"
                                label={Translator.trans(
                                  "customer_relationship_team.fields.parts_representative"
                                )}
                                userListName="partsRepresentatives"
                                component={renderReactVerticalSelect}
                                required
                              />
                            </Col>
                            <Col md="12" lg="4">
                              <PeopleSelect
                                name="serviceRepresentative"
                                label={Translator.trans(
                                  "customer_relationship_team.fields.service_representative"
                                )}
                                userListName="serviceRepresentatives"
                                component={renderReactVerticalSelect}
                                required
                              />
                            </Col>
                          </Row>
                        </div>
                      )}
                    </div>
                  )}
                  {formType !== "edition" && crtCreated && (
                    <div className="text-center">
                      <h1 style={{ color: "green" }}>
                        <i className="fa fa-fw fa-check" />
                      </h1>
                      <h1>CRT SAVED</h1>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
          {formType !== "edition" && crtCreated && (
            <div className="row">
              <div className="col-md-8">
                <div className="ibox float-e-margins">
                  <div className="ibox-title">
                    <h5>
                      {Translator.trans(
                        "customer_relationship_team.title.contact_role"
                      )}
                    </h5>
                  </div>
                  <div className="ibox-content">
                    <Row>
                      <Col sm="12">
                        <ExtranetUserAsyncSelect
                          name="extranetUser"
                          required
                          async={false}
                        />
                      </Col>
                      <Col sm="12">
                        <ExtranetUserGroupsSelect name="extranetUserGroups" />
                      </Col>
                    </Row>
                  </div>
                </div>
              </div>
            </div>
          )}
          <div className="row">
            <div className="col-sm-8 text-end">
              <button
                className={`btn btn-${
                  valid &&
                  (formType === "edition" || extranetUserListEmpty === false)
                    ? "info"
                    : "danger"
                } m-b-xl`}
                disabled={
                  !valid || (formType !== "edition" && extranetUserListEmpty)
                }
                type="submit"
              >
                Save
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
}

const formConfiguration = {
  form: "customer_relationship_team_form",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const mapStateToProps = (state: RootState, props: IProps) => {
  const { crt } = state;
  let initialValues = {};
  if (props.formType !== "creation" && _.has(crt, "details.id")) {
    initialValues = customerRelationshipTeamFactoryForm(
      crt.details,
      props.formType
    );
  }

  const actualFormValues: any = getFormValues(
    "customer_relationship_team_form"
  )(state);
  let initialFormValues =
    props.formType === "creation" ? actualFormValues : initialValues;

  if (props.formType === "duplicate" && actualFormValues) {
    initialFormValues = {
      ...initialFormValues,
      extranetUserGroups: actualFormValues.extranetUserGroups,
      extranetUser: actualFormValues.extranetUser,
      id: actualFormValues.id,
    };
  }

  initialValues = {
    ...initialValues,
    formType: props.formType,
  };

  return {
    initialValues,
    formType: props.formType,
    showCRTLoading: state.crt.showLoading,
    showExtranetUserAclLoading: state.extranetUser.showLoading,
    showCRTError: state.crt.showError,
    showCRTSuccess: state.crt.showSuccess,
    showExtranetUserAclSuccess: state.extranetUser.showSuccess,
    showExtranetUserAclError: state.extranetUser.showError,
    extranetUserListEmpty: state.extranetUser.extranetUserListEmpty,
    crtCreated: state.crt.crtCreated,
    submittedExtranetUserGroup: state.extranetUser.submittedExtranetUserGroup,
    formValues: initialFormValues,
    businessPartnerCodesChoice:
      getSelectedCustomerBusinessPartnerCodesMapping(state),
    errorMessage: state.crt.errorMessage,
    selectedCustomer: state.crt.selectedCustomer,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchCustomer: (iri: any) =>
      dispatch(fetchSalesCustomer(iri, SALES_CRT_FETCH_CUSTOMER)),
    writeCustomerRelationshipTeam: (customerRelationshipTeam: any) =>
      dispatch(
        writeCustomerRelationshipTeamAction(
          customerRelationshipTeam,
          "customer_relationship_team_form"
        )
      ),
    writeExtranetUserAcl: (extranetUserAcl: any) =>
      dispatch(
        writeExtranetUserAclAction(
          extranetUserAcl,
          "customer_relationship_team_form"
        )
      ),
    hideErrorMessage: (path: any) => dispatch(hideErrorAlert(path)),
    hideSuccessMessage: (path: any) => dispatch(hideSuccessAlert(path)),
    fetchExtranetUsersHierarchyList: (customer: any) =>
      dispatch(fetchExtranetUsersHierarchyListAction(customer)),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(
  reduxForm<IFormData, IProps>(formConfiguration)(CustomerRelationshipTeamForm)
);
