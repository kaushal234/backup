import React, { useEffect, useState } from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import {
  change,
  Field,
  getFormValues,
  InjectedFormProps,
  reduxForm,
} from "redux-form";
import Swal from "sweetalert2";
import { Row, Col } from "react-bootstrap";
import { renderVerticalSelect } from "../../components/Forms/Elements";
import ModulesSelect from "../../components/Forms/MIS/ModulesSelect";
import PeopleAsyncSelect from "../../components/Forms/PeopleAsyncSelect";
import Loader from "../../components/Loader";
import { hideErrorAlert, hideSuccessAlert } from "../../actions/genericActions";
import { taskFactory, taskFactoryForm } from "../../model/form/task/factory";
import { fetchModules as fetchModulesAction } from "../../actions/mis/modulesActions";
import { writeTask as writeTaskAction } from "../../actions/task/taskActions";
import validate from "../../model/form/task/validation";
import { AppDispatch, RootState } from "../../store";
import { useAppDispatch } from "../../hooks/hooks";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";
import { TASK_IFACTOR_OPTIONS } from "../../constants/constants";

type IFormData = any;

interface IProps {
  formType: any;
  fetchModules: any;
  showLoading: any;
  showError: any;
  showSuccess: any;
  formValues: any;
  errorMessage: any;
  hideErrorMessage: any;
  hideSuccessMessage: any;
  writeTask: any;
  disabled: any;
}

interface IMappedProps {
  module: any;
  referenceId: any;
  confidential: any;
  taskValues: any;
  indiceFactor?: string | null;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

function TaskForm({
  formType,
  disabled,
  fetchModules,
  handleSubmit,
  showLoading,
  showError,
  showSuccess,
  formValues,
  errorMessage,
  hideErrorMessage,
  hideSuccessMessage,
  writeTask,
  valid,
}: IWrappedProps) {
  const [redirectFlag, setRedirectFlag] = useState(false);

  useEffect(() => {
    fetchModules();
  }, []);

  const dispatch = useAppDispatch();

  useEffect(() => {
    if (formType !== "edit") {
      dispatch(change("task_form", "startedAt", new Date()));
    }
    dispatch(
      change("task_form", "dueDate", new Date(Date.now() + 24 * 60 * 60 * 1000))
    );
  }, []);

  useEffect(() => {
    if (showSuccess) {
      Swal.fire({
        icon: "success",
        title: "Saved",
        confirmButtonText: "OK",
      }).then(hideSuccessMessage("MIS"));
      setRedirectFlag(true);
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
      }).then(hideErrorMessage("MIS"));
    }
  }, [showError]);

  const onSubmit = (values: any) => {
    let currentCount = 0;
    const totalTasks = values.assignees.length;
    if (totalTasks <= 10) {
      Swal.fire({
        title: "Task in creation",
        text: `${currentCount}/${totalTasks}`,
      });
      values.assignees.forEach((assignee: any, index: any) => {
        setTimeout(() => {
          values = {
            ...values,
            assignee,
          };
          writeTask(taskFactory(values));
          currentCount++;

          Swal.update({
            text: `${currentCount}/${totalTasks}`,
          });
        }, index * 200);
      });
    } else {
      Swal.fire({
        icon: "warning",
        title: "Failed",
        text: Translator.trans("task.errors.too_many_assignee_selected"),
        confirmButtonText: "OK",
        confirmButtonColor: "#DD6B55",
      });
    }
  };

  const getMaxStartedAt = (dueDate: Date) => {
    if (!dueDate) return null;
    const maxDate = new Date(dueDate);
    maxDate.setDate(maxDate.getDate() - 1);
    return maxDate;
  };

  const getMinDueDate = (startedAt: Date) => {
    if (!startedAt) return null;
    const minDate = new Date(startedAt);
    minDate.setDate(minDate.getDate() + 1);
    return minDate;
  };

  if (redirectFlag) {
    window.location.href = `/en/private/tasks/${formValues.id}/show`;
    return <Loader />;
  }
  return (
    <div style={{ position: "relative" }}>
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
      <Row className="mt-5">
        <Col md="12" xl="9" xxl="8">
          <div className="card">
            <div className="card-header">
              <h3>
                {formType === "edit"
                  ? Translator.trans("task.edit_form_title")
                  : Translator.trans("task.add_form_title")}
              </h3>
            </div>
            <div className="card-body">
              <form
                onSubmit={handleSubmit(onSubmit)}
                style={{ opacity: showLoading ? 0.1 : 1 }}
              >
                <Field name="id" type="hidden" component="input" />
                <Row>
                  <Col md="10">
                    <GenericFormComponent
                      type="Field"
                      name="shortDescription"
                      label={Translator.trans("task.fields.short_description")}
                      required
                    />
                  </Col>
                  <Col md="2">
                    <GenericFormComponent
                      type="Switch"
                      name="confidential"
                      required
                      label={Translator.trans("task.fields.confidential")}
                      centered
                      isVertical
                    />
                  </Col>
                  <Col md="12">
                    <Row>
                      <GenericFormComponent
                        type="RichTextField"
                        name="description"
                        label={Translator.trans("fields.description")}
                        required
                      />
                    </Row>
                    <Row>
                      <Col md="12" lg="6">
                        <ModulesSelect
                          name="module"
                          required
                          isDisabled={disabled}
                        />
                      </Col>
                      <Col md="12" lg="3">
                        <GenericFormComponent
                          type="Field"
                          name="escalationTrigger"
                          required
                          label={Translator.trans(
                            "task.fields.escalation_trigger"
                          )}
                          allowFloatsOnly
                        />
                      </Col>
                      <Col md="12" lg="3">
                        <Field
                          name="escalationTriggerUnit"
                          label={Translator.trans("task.fields.unit")}
                          required
                          component={renderVerticalSelect}
                        >
                          <option value="" disabled />
                          {["DAYS", "WEEKS", "MONTHS", "YEARS"].map((type) => (
                            <option value={type} key={type}>
                              {type}
                            </option>
                          ))}
                        </Field>
                      </Col>
                    </Row>
                    <Row>
                      <Col md="12" lg="3">
                        <GenericFormComponent
                          type="Field"
                          allowFloatsOnly
                          name="referenceId"
                          required
                          label={Translator.trans("task.fields.reference_id")}
                          disabled={disabled}
                        />
                      </Col>
                      <Col md="12" lg="3">
                        <GenericFormComponent
                          type="DatePicker"
                          name="startedAt"
                          required
                          label={Translator.trans("task.fields.started_date")}
                          dateformat="Y-M-D"
                          min={new Date()}
                          max={getMaxStartedAt(formValues?.dueDate)}
                        />
                      </Col>
                      {formType !== "edit" && (
                        <Col md="12" lg="3">
                          <GenericFormComponent
                            type="DatePicker"
                            name="dueDate"
                            required
                            label={Translator.trans("task.fields.due_date")}
                            dateformat="Y-M-D"
                            min={getMinDueDate(formValues?.startedAt)}
                          />
                        </Col>
                      )}
                      <Col md="12" lg="3">
                        <Field
                          name="indiceFactor"
                          label={Translator.trans("fields.ifactor")}
                          required
                          component={renderVerticalSelect}
                        >
                          <option value="" disabled />
                          {TASK_IFACTOR_OPTIONS.map(({ value, label }) => (
                            <option value={value} key={value}>
                              {label}
                            </option>
                          ))}
                        </Field>
                      </Col>
                    </Row>
                    <Row>
                      <Col md="12" lg="12">
                        <PeopleAsyncSelect
                          name="assignees"
                          label={Translator.trans("task.fields.assignee")}
                          required
                          isMulti={formType !== "edit" && true}
                        />
                        {Translator.trans(
                          "task.message.multi_assignee_selected"
                        )}
                      </Col>
                    </Row>
                  </Col>
                </Row>
                <Row>
                  <Col md="12">
                    <PeopleAsyncSelect
                      name="recipients"
                      label={Translator.trans("task.fields.ccs")}
                      isMulti
                    />
                  </Col>
                </Row>
                <Row className="mt-3">
                  <Col sm="12" className="text-end">
                    <button
                      className={`btn btn-${valid ? "info" : "danger"} m-b-xl`}
                      type="submit"
                      disabled={!valid}
                    >
                      <i className="fa fa-fw fa-save" />
                      &nbsp;Submit
                    </button>
                  </Col>
                </Row>
              </form>
            </div>
          </div>
        </Col>
      </Row>
    </div>
  );
}

const formConfiguration = {
  form: "task_form",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const mapStateToProps = (state: RootState, props: IProps & IMappedProps) => {
  let initialValues: any = {
    escalationTrigger: 60,
    escalationTriggerUnit: "DAYS",
    indiceFactor: props.indiceFactor || "IF 1",
    module: props.module
      ? {
          value: props.module["@id"],
          application: props.module.application,
          label: `${props.module.name}: ${props.module.shortDescription} - ${props.module.application.name}`,
        }
      : null,
    referenceId: props.referenceId ?? null,
    confidential: props.confidential ?? false,
  };
  if (props.formType === "edit") {
    initialValues = taskFactoryForm(props.taskValues);
  }
  return {
    initialValues,
    showLoading: state.task.showLoading,
    showSuccess: state.task.showSuccess,
    showError: state.task.showError,
    errorMessage: state.task.errorMessage,
    formValues: getFormValues("task_form")(state),
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchModules: () => dispatch(fetchModulesAction()),
    writeTask: (taskValues: any) =>
      dispatch(writeTaskAction(taskValues, formConfiguration.form)),
    hideErrorMessage: (path: any) => dispatch(hideErrorAlert(path)),
    hideSuccessMessage: (path: any) => dispatch(hideSuccessAlert(path)),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(TaskForm));
