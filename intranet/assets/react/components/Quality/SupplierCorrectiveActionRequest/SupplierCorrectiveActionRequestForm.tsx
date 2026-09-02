import React, { useEffect, useState } from "react";
import { Row, Col } from "react-bootstrap";
import _ from "lodash";
import { Field, formValueSelector } from "redux-form";
import Translator from "bazinga-translator";
import { connect, useSelector } from "react-redux";
import Swal from "sweetalert2";
import {
  renderReactVerticalSelect,
  renderVerticalSelect,
} from "../../Forms/Elements";
import FactoriesSelect from "../../Forms/FactoriesSelect";
import PeopleAsyncSelect from "../../Forms/PeopleAsyncSelect";
import BusinessPartnersSelect from "../../Forms/BusinessPartnersSelect";
import { AppDispatch, RootState } from "../../../store";
import { fetchFactories as fetchFactoriesAction } from "../../../actions/location/LocationActions";
import {
  hideErrorAlert,
  hideSuccessAlert,
} from "../../../actions/genericActions";
import { IFactory } from "../../../types/ISupplierCorrectiveActionRequestFactoryProps";
import GenericFormComponent from "../../GenericFormComponent/GenericFormComponent";

interface IProps {
  fetchFactories: () => void;
  showError: boolean;
  hideSuccessMessage: (path: string) => { type: string };
  hideErrorMessage: (path: string) => { type: string };
  showSuccess: boolean;
  formName: string;
  errorMessage: string;
}

function SupplierCorrectiveActionRequestForm({
  fetchFactories,
  showError,
  hideSuccessMessage,
  hideErrorMessage,
  showSuccess,
  formName,
  errorMessage,
}: IProps) {
  const [factorySelected, setFactorySelected] = useState<Partial<IFactory>>({});
  const factoryValue = useSelector((state: RootState) =>
    formValueSelector(formName)(state, "factory")
  );
  useEffect(() => {
    fetchFactories();
  }, []);
  useEffect(() => {
    setFactorySelected(factoryValue);
  }, [factoryValue]);
  useEffect(() => {
    if (showSuccess) {
      Swal.fire({
        icon: "success",
        title: "Saved",
        confirmButtonText: "OK",
      }).then(() => hideSuccessMessage("SCAR"));
    }
  }, [showSuccess]);
  useEffect(() => {
    if (showError) {
      Swal.fire({
        icon: "warning",
        title: "Failed",
        text: errorMessage,
        confirmButtonText: "OK",
        confirmButtonColor: "#DD6B55",
      }).then(() => hideErrorMessage("SCAR"));
    }
  }, [showError]);
  return (
    <>
      <Row>
        <Col md={4}>
          <FactoriesSelect />
        </Col>
        {!_.isEmpty(factorySelected) && (
          <>
            <Col md={4}>
              <Field
                name="importanceFactor"
                label={Translator.trans(
                  "supplier_corrective_action_request.fields.importance_factor"
                )}
                required
                component={renderVerticalSelect}
              >
                <option value="" disabled />
                {["IF 1", "IF 10", "IF 100", "IF 1000"].map((type) => (
                  <option value={type.replace(" ", "")} key={type}>
                    {type}
                  </option>
                ))}
              </Field>
            </Col>
            <Col md={4}>
              <GenericFormComponent
                type="Field"
                name="shortDescription"
                label={Translator.trans(
                  "supplier_corrective_action_request.fields.short_description"
                )}
                required
              />
            </Col>
          </>
        )}
      </Row>
      {!_.isEmpty(factorySelected) && (
        <>
          <Row>
            <GenericFormComponent
              type="RichTextField"
              name="description"
              label={Translator.trans(
                "supplier_corrective_action_request.fields.description"
              )}
              required
            />
          </Row>
          <Row>
            <Col md={4}>
              <PeopleAsyncSelect
                name="representative"
                label={Translator.trans(
                  "supplier_corrective_action_request.fields.representative"
                )}
                customFilter="acls.group.name[0]=gg_ENG&acls.group.name[1]=gg_QUALITY"
                required
              />
            </Col>
            <Col md={4}>
              <PeopleAsyncSelect
                name="leader"
                label={Translator.trans(
                  "supplier_corrective_action_request.fields.leader"
                )}
              />
            </Col>
            <Col md={4}>
              <BusinessPartnersSelect
                component={renderReactVerticalSelect}
                name="supplierNumber"
                required
                filter={factorySelected.erp === 390 ? "sage" : "supplier"}
                label={Translator.trans(
                  "supplier_corrective_action_request.fields.supplier_number"
                )}
              />
            </Col>
          </Row>
        </>
      )}
    </>
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    showError: state.supplierCorrectiveActionRequest.showError,
    errorMessage: state.supplierCorrectiveActionRequest.errorMessage,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchFactories: () => dispatch(fetchFactoriesAction()),
    hideErrorMessage: (path: string) => dispatch(hideErrorAlert(path)),
    hideSuccessMessage: (path: string) => dispatch(hideSuccessAlert(path)),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(SupplierCorrectiveActionRequestForm);
