import React from "react";
import {
  getFormValues,
  reduxForm,
  FieldArray,
  InjectedFormProps,
} from "redux-form";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import { Badge, Stack } from "react-bootstrap";
import Swal from "sweetalert2";
import validate from "../../model/form/technician_on_call/batchValidation";
import Loader from "../../components/Loader";
import TocClonesArray from "../../components/TechnicianOnCall/TocClonesArray";
import { duplicateTechnicianOnCallFactory } from "../../model/form/technician_on_call/factory";
import { RootState } from "../../store";
import { postTechnicianOnCallDuplicate } from "../../api/postTechnicianOnCallDuplicate";
import { IPostTechnicalOnCallDuplicateApiResponse } from "../../types/IPostTechnicalOnCallDuplicateApiResponse";
import { IBasicApiResponse } from "../../types/IBasicApiResponse";
import { useAppDispatch } from "../../hooks/hooks";
import {
  tocHideErrorAlert,
  tocHideSuccessAlert,
} from "../../reducers/technicianOnCall/technicianOnCallReducer";

const formName = "technician_on_call_duplicate_form";

type IFormData = any;

interface IProps {
  showSuccess: any;
  showError: any;
  technicianOnCall: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

const getMessage = (
  response: IBasicApiResponse<IPostTechnicalOnCallDuplicateApiResponse>
) => {
  const lines = response.data?.outputLines ?? [];

  const okLines = lines.filter((line) => line.technicianOnCallId);
  const tocErrors = lines.filter((line) => line.technicianOnCallErrorMessage);
  const csrErrors = lines.filter(
    (line) =>
      !line.technicianOnCallErrorMessage &&
      line.customerServiceRecordErrorMessage
  );

  const successMessages = [
    Translator.trans("toc.messages.success.duplicate", {
      count: okLines.length,
    }),
  ];

  okLines.forEach((line) => {
    const messageFragments = [
      `SN# ${line.serialNumber} : TOC #${line.technicianOnCallId}`,
    ];

    if (line.customerServiceRecordId) {
      messageFragments.push(`CSR #${line.customerServiceRecordId}`);
    }

    if (line.customerServiceRecordErrorMessage) {
      messageFragments.push(
        `CSR error: ${line.customerServiceRecordErrorMessage}`
      );
    }

    successMessages.push(messageFragments.join(" / "));
  });

  if (response.status === 206) {
    if (tocErrors.length) {
      successMessages.push(`TOC errors: ${tocErrors.length}`);
    }
    if (csrErrors.length) {
      successMessages.push(`CSR errors: ${csrErrors.length}`);
    }
  }

  return successMessages;
};

function TechnicianOnCallDuplicateForm(props: IWrappedProps) {
  const {
    technicianOnCall,
    showSuccess,
    showError,
    valid,
    submitting,
    handleSubmit,
  } = props;

  const dispatch = useAppDispatch();

  const [redirectFlag, setRedirectFlag] = React.useState(false);

  const onSubmit = async (formValues: any) => {
    const batch = duplicateTechnicianOnCallFactory(formValues);
    const response = await postTechnicianOnCallDuplicate({
      id: technicianOnCall.id ?? "",
      data: batch,
    });
    if (response.status === 201 || response.status === 206) {
      const messages = getMessage(response);
      let template = "";
      messages.forEach((message) => {
        template += `<p>${message}</p>`;
      });
      Swal.fire({
        icon: "success",
        title: "Saved",
        html: template,
        confirmButtonText: "OK",
      }).then(() => {
        dispatch(tocHideSuccessAlert());
        setRedirectFlag(true);
      });
    } else {
      Swal.fire({
        icon: "warning",
        text: response.errorMessage || "",
        title: `Creation failed`,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "OK",
      }).then(() => {
        dispatch(tocHideErrorAlert());
      });
    }
  };

  if (redirectFlag) {
    window.location.href = `/en/private/service/technician-on-calls/${technicianOnCall.id}/show`;
    return <Loader />;
  }

  return (
    <div style={{ position: "relative" }}>
      <Row>
        <Col className="col-3">
          <div className="ibox">
            <h2 className="ibox-title">
              {Translator.trans("toc.title.duplicate.source_toc_data", {
                id: technicianOnCall.id,
              })}
            </h2>
            <div className="ibox-content">
              <dl className="row m-2">
                <dt>{Translator.trans("toc.fields.title")}</dt>
                <dd>{technicianOnCall.title}</dd>
                <hr className="mt-1" />
                <dt>{Translator.trans("toc.fields.description")}</dt>
                <dd
                  // eslint-disable-next-line react/no-danger
                  dangerouslySetInnerHTML={{
                    __html: technicianOnCall.description,
                  }}
                />
                <hr className="mt-1" />
                <dt className="col-4 text-end">
                  {Translator.trans("tasks.assignee")}
                </dt>
                {technicianOnCall.assignee ? (
                  <dd className="col-8">
                    <p>{`${technicianOnCall.assignee.lastname}, ${technicianOnCall.assignee.firstname} (${technicianOnCall.assignee.username})`}</p>
                  </dd>
                ) : (
                  <dd />
                )}
                <hr className="mt-1" />
                <dt className="col-4 text-end">
                  {Translator.trans("fields.ifactor")}
                </dt>
                <dd className="col-8">{technicianOnCall.indiceFactor}</dd>
                <hr className="mt-1" />
                <dt className="col-4 text-end">
                  {Translator.trans("toc.fields.error_codes.label")}
                </dt>
                <dd className="col-8">{technicianOnCall.errorCodes}</dd>
                <hr className="mt-1" />
                <dt className="col-4 text-end">
                  {Translator.trans("toc.fields.technician_on_call_type")}
                </dt>
                <dd className="col-8">
                  {technicianOnCall.technicianOnCallType?.description}
                </dd>
                <hr className="mt-1" />
                <dt className="col-4 text-end">
                  {Translator.trans("toc.fields.service_activity")}
                </dt>
                <dd className="col-8">
                  {technicianOnCall.serviceActivity?.description}
                </dd>
                <hr className="mt-1" />
                <dt className="col-4 text-end">
                  {Translator.trans("toc.fields.unit_operational_status")}
                </dt>
                <dd className="col-8">
                  {technicianOnCall.unitOperationalStatus?.description}
                </dd>
                <hr className="mt-1" />
                <dt className="col-4 text-end">
                  {Translator.trans("toc.fields.tags.label")}
                </dt>
                <dd className="col-8">
                  <Stack
                    className="d-flex flex-wrap"
                    direction="horizontal"
                    gap={1}
                  >
                    {technicianOnCall.tags.map((tag: any) => (
                      <Badge key={tag["@id"]} bg="secondary" text="light">
                        {Translator.trans(tag.name)}
                      </Badge>
                    ))}
                  </Stack>
                </dd>
              </dl>
            </div>
          </div>
        </Col>
        <Col className="col-9">
          <div className="ibox">
            <h2 className="ibox-title">
              {Translator.trans("toc.title.duplicate.select_equipment_record")}
            </h2>
            <div className="ibox-content">
              <form
                className="form-condensed"
                onSubmit={handleSubmit(onSubmit)}
                style={{
                  opacity: submitting || showSuccess || showError ? 0.3 : 1,
                  pointerEvents:
                    submitting || showSuccess || showError ? "none" : "auto",
                }}
              >
                <FieldArray
                  rerenderOnEveryChange
                  name="technicianOnCallClones"
                  component={TocClonesArray}
                />
                <div className="col-12 d-flex justify-content-end">
                  <button
                    className={`btn btn-${valid ? "info" : "danger"} my-4`}
                    type="submit"
                    disabled={submitting || !valid}
                  >
                    <i className="fa fa-fw fa-save" />
                    &nbsp;Save All
                  </button>
                </div>
              </form>
            </div>
          </div>
        </Col>
      </Row>
    </div>
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    formValues: getFormValues(formName)(state),
    showSuccess: state.technicianOnCall.showSuccess,
    showError: state.technicianOnCall.showError,
  };
};

export default connect(mapStateToProps)(
  reduxForm<IFormData, IProps>({
    form: formName,
    enableReinitialize: true,
    keepDirtyOnReinitialize: true,
    validate,
  })(TechnicianOnCallDuplicateForm)
);
