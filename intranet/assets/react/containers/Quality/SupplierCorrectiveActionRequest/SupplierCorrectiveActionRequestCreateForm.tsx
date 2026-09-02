import React from "react";
import { connect } from "react-redux";
import { InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import { Row, Col, Form } from "react-bootstrap";
import {
  supplierCorrectiveActionRequestFactory,
  supplierCorrectiveActionRequestPrefillFactoryForm,
} from "../../../model/form/quality/supplierCorrectiveActionRequest/factory";
import { createSupplierCorrectiveActionRequest as createSupplierCorrectiveActionRequestAction } from "../../../actions/quality/SupplierCorrectiveActionRequestAction";
import { AppDispatch, RootState } from "../../../store";
import validate from "../../../model/form/quality/supplierCorrectiveActionRequest/validation";
import SupplierCorrectiveActionRequestForm from "../../../components/Quality/SupplierCorrectiveActionRequest/SupplierCorrectiveActionRequestForm";
import { SupplierCorrectiveActionRequestFile } from "../../../components/Quality/SupplierCorrectiveActionRequest/SupplierCorrectiveActionRequestFile";
import { ISupplierCorrectiveActionRequestAction } from "../../../types/ISupplierCorrectiveActionRequestAction";
import Loader from "../../../components/Loader";
import { ISupplierCorrectiveActionRequestPrefillProps } from "../../../types/ISupplierCorrectiveActionRequestPrefillProps";
import { ISupplierCorrectiveActionRequestFactoryProps } from "../../../types/ISupplierCorrectiveActionRequestFactoryProps";

const FORM_NAME = "supplier_corrective_action_request_create_form";
type IFormData = any;

interface IProps {
  createSupplierCorrectiveActionRequest: (
    values: ISupplierCorrectiveActionRequestAction
  ) => void;
  showSuccess: boolean;
  supplierCorrectiveActionRequestId?: number | null;
  valid: boolean;
  showLoading: boolean;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

function SupplierCorrectiveActionRequestCreateForm({
  createSupplierCorrectiveActionRequest,
  handleSubmit,
  showSuccess,
  supplierCorrectiveActionRequestId,
  valid,
  showLoading,
}: IWrappedProps) {
  const onSubmit = (values: ISupplierCorrectiveActionRequestFactoryProps) => {
    createSupplierCorrectiveActionRequest(
      supplierCorrectiveActionRequestFactory(values)
    );
  };
  return (
    <div style={{ position: "relative" }}>
      {showLoading && (
        <Loader
          style={{
            position: "absolute",
            top: "50%",
            left: "50%",
            transform: "translate(-50%, -50%)",
            zIndex: 20,
          }}
          childStyle={undefined}
        />
      )}
      <Row className="mt-5">
        <Col md={!showSuccess ? 12 : 6}>
          <Form
            onSubmit={handleSubmit(onSubmit)}
            style={{
              opacity: showSuccess ? 0.3 : 1,
              pointerEvents: showSuccess ? "none" : "auto",
            }}
          >
            <div className="card">
              <div className="card-header">
                <h3>
                  {Translator.trans(
                    "supplier_corrective_action_request.add.title"
                  )}
                </h3>
              </div>
              <Row className="m-2">
                <SupplierCorrectiveActionRequestForm
                  formName={FORM_NAME}
                  showSuccess={showSuccess}
                />
                <Row>
                  <Col sm="12" className="text-end mt-3">
                    <button
                      className={`btn btn-${valid ? "info" : "danger"} m-b-xl`}
                      type="submit"
                      disabled={!valid || showSuccess}
                    >
                      <i className="fa fa-fw fa-save" />
                      &nbsp;Submit
                    </button>
                  </Col>
                </Row>
              </Row>
            </div>
          </Form>
        </Col>
        {showSuccess && (
          <SupplierCorrectiveActionRequestFile
            id={supplierCorrectiveActionRequestId}
          />
        )}
      </Row>
    </div>
  );
}

const formConfiguration = {
  form: FORM_NAME,
  validate,
};

const mapStateToProps = (state: RootState) => {
  return {
    showLoading: state.supplierCorrectiveActionRequest.showLoading,
    showSuccess: state.supplierCorrectiveActionRequest.showSuccess,
    supplierCorrectiveActionRequestId: state.supplierCorrectiveActionRequest.id,
  };
};

const mapDispatchToProps = (
  dispatch: AppDispatch,
  props: ISupplierCorrectiveActionRequestPrefillProps
) => {
  const { prefilledDataFromNonConformityRecord } = props;
  return {
    initialValues: prefilledDataFromNonConformityRecord
      ? supplierCorrectiveActionRequestPrefillFactoryForm(
          prefilledDataFromNonConformityRecord
        )
      : {},
    createSupplierCorrectiveActionRequest: (
      values: ISupplierCorrectiveActionRequestAction
    ) => dispatch(createSupplierCorrectiveActionRequestAction(values)),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(
  reduxForm<IFormData, IProps>(formConfiguration)(
    SupplierCorrectiveActionRequestCreateForm
  )
);
