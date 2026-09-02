import React from "react";
import { connect } from "react-redux";
import { InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import { Row, Col, Form } from "react-bootstrap";
import { SnackbarProvider } from "notistack";
import SupplierCorrectiveActionRequestForm from "../../../components/Quality/SupplierCorrectiveActionRequest/SupplierCorrectiveActionRequestForm";
import {
  supplierCorrectiveActionRequestFactory,
  supplierCorrectiveActionRequestFactoryForm,
} from "../../../model/form/quality/supplierCorrectiveActionRequest/factory";

import { AppDispatch, RootState } from "../../../store";
import { updateSupplierCorrectiveActionRequest as updateSupplierCorrectiveActionRequestAction } from "../../../actions/quality/SupplierCorrectiveActionRequestAction";
import {
  ISupplierCorrectiveActionRequestMainFile,
  ISupplierCorrectiveActionRequestPropsApi,
} from "../../../types/ISupplierCorrectiveActionRequestPropsApi";
import { ISupplierCorrectiveActionRequestAction } from "../../../types/ISupplierCorrectiveActionRequestAction";
import { SupplierCorrectiveActionRequestFile } from "../../../components/Quality/SupplierCorrectiveActionRequest/SupplierCorrectiveActionRequestFile";
import Loader from "../../../components/Loader";
import validate from "../../../model/form/quality/supplierCorrectiveActionRequest/validation";
import { ISupplierCorrectiveActionRequestFactoryProps } from "../../../types/ISupplierCorrectiveActionRequestFactoryProps";
import ToastCloseIcon from "../../../components/ToastCloseIcon/ToastCloseIcon";
import GenericFormComponent from "../../../components/GenericFormComponent/GenericFormComponent";

const FORM_NAME = "supplier_corrective_action_request_update_form";
type IFormData = any;

interface IProps {
  updateSupplierCorrectiveActionRequest: (
    values: ISupplierCorrectiveActionRequestAction
  ) => void;
  valid: boolean;
  showSuccess: boolean;
  supplierCorrectiveActionRequestId?: number | null;
  showLoading: boolean;
  supplierCorrectiveActionRequestMainFile: ISupplierCorrectiveActionRequestMainFile | null;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

function SupplierCorrectiveActionRequestUpdateForm({
  updateSupplierCorrectiveActionRequest,
  handleSubmit,
  showSuccess,
  supplierCorrectiveActionRequestId,
  valid,
  showLoading,
  supplierCorrectiveActionRequestMainFile,
}: IWrappedProps) {
  const onSubmit = (values: ISupplierCorrectiveActionRequestFactoryProps) => {
    updateSupplierCorrectiveActionRequest(
      supplierCorrectiveActionRequestFactory(values)
    );
  };
  return (
    <div style={{ position: "relative" }}>
      <SnackbarProvider maxSnack={3} action={ToastCloseIcon} />
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
                    "supplier_corrective_action_request.edit.title"
                  )}
                </h3>
              </div>
              <Row className="m-2">
                <SupplierCorrectiveActionRequestForm
                  formName={FORM_NAME}
                  showSuccess={showSuccess}
                />
                <Row className="py-1">
                  <Col md={6}>
                    <GenericFormComponent
                      type="Field"
                      label={Translator.trans(
                        "supplier_corrective_action_request.fields.root_cause"
                      )}
                      name="issueOrigin"
                      isTextArea
                    />
                  </Col>
                  <Col md={6}>
                    <GenericFormComponent
                      type="Field"
                      label={Translator.trans(
                        "supplier_corrective_action_request.fields.corrective_actions"
                      )}
                      name="correctiveAction"
                      isTextArea
                    />
                  </Col>
                </Row>
                <Row className="py-1">
                  <Col md={6}>
                    <GenericFormComponent
                      type="Field"
                      label={Translator.trans(
                        "supplier_corrective_action_request.fields.preventive_actions"
                      )}
                      name="preventiveAction"
                      isTextArea
                    />
                  </Col>
                  <Col md={6}>
                    <GenericFormComponent
                      type="Field"
                      label={Translator.trans(
                        "supplier_corrective_action_request.fields.commercial_agreement"
                      )}
                      name="commercialAgreement"
                      isTextArea
                    />
                  </Col>
                </Row>
                <Row className="py-1">
                  <Col md={6}>
                    <GenericFormComponent
                      type="Field"
                      label={Translator.trans(
                        "supplier_corrective_action_request.fields.verification_description"
                      )}
                      name="verificationDescription"
                      isTextArea
                    />
                  </Col>
                  <Col md={6}>
                    <GenericFormComponent
                      type="Field"
                      label={Translator.trans(
                        "supplier_corrective_action_request.fields.conclusion"
                      )}
                      name="conclusion"
                      isTextArea
                    />
                  </Col>
                </Row>
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
            mainFile={supplierCorrectiveActionRequestMainFile}
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

const mapStateToProps = (
  state: RootState,
  props: ISupplierCorrectiveActionRequestPropsApi
) => {
  const { supplierCorrectiveActionRequest } = props;

  return {
    initialValues: supplierCorrectiveActionRequestFactoryForm(
      supplierCorrectiveActionRequest
    ),
    showLoading: state.supplierCorrectiveActionRequest.showLoading,
    showSuccess: state.supplierCorrectiveActionRequest.showSuccess,
    supplierCorrectiveActionRequestId: state.supplierCorrectiveActionRequest.id,
    supplierCorrectiveActionRequestMainFile:
      supplierCorrectiveActionRequest.mainFile,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    updateSupplierCorrectiveActionRequest: (
      values: ISupplierCorrectiveActionRequestAction
    ) => dispatch(updateSupplierCorrectiveActionRequestAction(values)),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(
  reduxForm<IFormData, IProps>(formConfiguration)(
    SupplierCorrectiveActionRequestUpdateForm
  )
);
