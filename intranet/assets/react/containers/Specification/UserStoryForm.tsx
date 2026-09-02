import React, { useState, useEffect } from "react";
import { connect } from "react-redux";
import { Button, Row, Col } from "react-bootstrap";
import Translator from "bazinga-translator";
import {
  Field,
  FieldArray,
  reduxForm,
  SubmissionError,
  getFormValues,
  InjectedFormProps,
} from "redux-form";
import _ from "lodash";
import Swal from "sweetalert2";
import { renderVerticalSelect } from "../../components/Forms/Elements";
import validate from "../../model/form/user_story/validation";
import RoleAccessLinesArray from "../../components/roleAccess/RoleAccessLinesArray";
import {
  userStoryFactory,
  userStoryFactoryForm,
} from "../../model/form/user_story/factory";
import { writeUserStory as writeUserStoryAction } from "../../actions/userStory/userStoryRequestAction";
import { hideErrorAlert, hideSuccessAlert } from "../../actions/genericActions";
import PeoplePropertyLinesArray from "../../components/peopleProperty/PeoplePropertyLineArray";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";

type IFormData = any;

interface IProps {
  formType: any;
  moduleName: any;
  writeUserStory?: any;
  specificationId?: any;
  showSuccess?: any;
  showFailed?: any;
  errorMessage?: any;
  hideErrorMessage?: any;
  hideSuccessMessage?: any;
  onCloseForm: any;
}

interface IMappedProps {
  userStoryValues?: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

function UserStoryForm({
  initialValues,
  handleSubmit,
  formType,
  moduleName,
  writeUserStory,
  specificationId,
  showSuccess,
  showFailed,
  errorMessage,
  hideErrorMessage,
  hideSuccessMessage,
  onCloseForm,
}: IWrappedProps) {
  const [emailBoxCheck, setEmailBoxCheck] = useState(false);
  const [notificationBoxCheck, setNotificationBoxCheck] = useState(false);
  const [isFirstRender, setIsFirstRender] = useState(true);

  useEffect(() => {
    if (isFirstRender && initialValues.checkBoxEmail !== undefined) {
      setEmailBoxCheck(initialValues.checkBoxEmail);
      setIsFirstRender(false);
    }
  }, [initialValues]);

  useEffect(() => {
    if (isFirstRender && initialValues.checkBoxNotification !== undefined) {
      setNotificationBoxCheck(initialValues.checkBoxNotification);
      setIsFirstRender(false);
    }
  }, [initialValues]);

  const handleCheckboxEmailChange = (event: any) => {
    setEmailBoxCheck(event.target.checked);
  };

  const handleCheckboxNotificationChange = (event: any) => {
    setNotificationBoxCheck(event.target.checked);
  };

  useEffect(() => {
    if (showSuccess) {
      Swal.fire({
        icon: "success",
        title: "Saved",
        confirmButtonText: "OK",
      }).then(hideSuccessMessage);
      onCloseForm();
    }
    if (showFailed) {
      Swal.fire({
        icon: "warning",
        text: errorMessage || "",
        title: "Creation failed",
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "OK",
      }).then(hideErrorMessage);
      onCloseForm();
    }
  }, [showSuccess, showFailed]);

  const onSubmit = (values: any) => {
    const errors = validate(values);
    if (!_.isEmpty(errors)) {
      throw new SubmissionError(errors);
    }
    if (formType === "creation") {
      writeUserStory(userStoryFactory(values, specificationId));
    } else {
      writeUserStory(userStoryFactory(values));
    }
  };

  return (
    <Row>
      <Col>
        <div className="ibox-title d-flex justify-content-between align-items-center">
          <h5>
            {formType === "creation"
              ? Translator.trans("mis.specification.user_story_form.create")
              : Translator.trans("mis.specification.user_story_form.edit")}
            &nbsp; {moduleName}
          </h5>
          <Button
            variant="danger"
            className="p-1"
            onClick={() => onCloseForm()}
          >
            <i className="fa-sharp fa-solid fa-circle-xmark fa-xl" />
          </Button>
        </div>
        <div className="ibox-content">
          <form onSubmit={handleSubmit(onSubmit)} className="ibox">
            <Col className="col-3">
              <Field
                component={renderVerticalSelect}
                name="category"
                label={Translator.trans(
                  "mis.specification.user_story_form.category"
                )}
                required
              >
                <option value="" disabled />
                {["CREATE", "READ", "UPDATE", "DELETE", "REPORT"].map(
                  (type) => (
                    <option value={type} key={type}>
                      {type}
                    </option>
                  )
                )}
              </Field>
            </Col>
            <Col>
              <GenericFormComponent
                type="RichTextField"
                required
                label={Translator.trans(
                  "mis.specification.user_story_form.user_story"
                )}
                name="description"
              />
            </Col>
            <h3 className="pt-3">
              {Translator.trans("mis.specification.user_story_form.security")}
            </h3>
            <hr />
            <FieldArray
              name="peopleProperties"
              btnName={Translator.trans(
                "mis.specification.user_story_form.people_property"
              )}
              component={PeoplePropertyLinesArray}
            />
            <FieldArray
              name="roleAccesses"
              btnName={Translator.trans(
                "mis.specification.user_story_form.role_access"
              )}
              component={RoleAccessLinesArray}
            />
            <Col className="mt-3">
              <hr />
              <h3>
                <GenericFormComponent
                  type="Checkbox"
                  onChange={handleCheckboxEmailChange}
                  label={Translator.trans(
                    "mis.specification.user_story_form.checkboxEmail"
                  )}
                  name="checkBoxEmail"
                  fitContent
                  checkboxFirst
                />
              </h3>
            </Col>
            {emailBoxCheck && (
              <>
                <Col>
                  <GenericFormComponent
                    type="Field"
                    required
                    label={Translator.trans(
                      "mis.specification.user_story_form.object_email"
                    )}
                    name="objectEmail"
                  />
                </Col>
                <Col>
                  <GenericFormComponent
                    type="Field"
                    isTextArea
                    required
                    label={Translator.trans(
                      "mis.specification.user_story_form.email"
                    )}
                    name="bodyEmail"
                  />
                </Col>
                <h3 className="pt-3">
                  {Translator.trans(
                    "mis.specification.user_story_form.email_recipients"
                  )}
                </h3>
                <hr />
                <Col>
                  <h3>
                    <GenericFormComponent
                      type="Checkbox"
                      label={Translator.trans(
                        "mis.specification.user_story_form.follower"
                      )}
                      name="emailFollower"
                      fitContent
                      checkboxFirst
                    />
                  </h3>
                </Col>
                <FieldArray
                  name="peoplePropertyEmail"
                  btnName={Translator.trans(
                    "mis.specification.user_story_form.people_property"
                  )}
                  component={PeoplePropertyLinesArray}
                />
                <FieldArray
                  name="roleRecipientEmail"
                  btnName={Translator.trans(
                    "mis.specification.user_story_form.recipient_notify"
                  )}
                  component={RoleAccessLinesArray}
                />
                <FieldArray
                  name="roleCopyEmail"
                  btnName={Translator.trans(
                    "mis.specification.user_story_form.copy_notify"
                  )}
                  component={RoleAccessLinesArray}
                />
                <hr />
              </>
            )}
            <h3>
              <GenericFormComponent
                type="Checkbox"
                onChange={handleCheckboxNotificationChange}
                label={Translator.trans(
                  "mis.specification.user_story_form.checkboxNotification"
                )}
                name="checkBoxNotification"
                fitContent
                checkboxFirst
              />
            </h3>
            {notificationBoxCheck && (
              <>
                <Col>
                  <GenericFormComponent
                    type="Field"
                    isTextArea
                    required
                    label={Translator.trans(
                      "mis.specification.user_story_form.message"
                    )}
                    name="notification"
                  />
                </Col>
                <h3 className="pt-3">
                  {Translator.trans(
                    "mis.specification.user_story_form.role_to_notify"
                  )}
                </h3>
                <hr />
                <Col>
                  <h3>
                    <GenericFormComponent
                      type="Checkbox"
                      label={Translator.trans(
                        "mis.specification.user_story_form.follower"
                      )}
                      name="notificationFollower"
                      fitContent
                      checkboxFirst
                    />
                  </h3>
                </Col>
                <FieldArray
                  name="roleRecipientNotify"
                  btnName={Translator.trans(
                    "mis.specification.user_story_form.role_to_notify"
                  )}
                  component={RoleAccessLinesArray}
                />
              </>
            )}
            <Col className="d-flex justify-content-end">
              <Button variant="primary" type="submit">
                {formType === "creation"
                  ? Translator.trans(
                      "mis.specification.user_story_form.btn.add"
                    )
                  : Translator.trans(
                      "mis.specification.user_story_form.btn.edit"
                    )}
              </Button>
            </Col>
          </form>
        </div>
      </Col>
    </Row>
  );
}

const formConfiguration = {
  form: "user_story_form",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const mapStateToProps = (state: RootState, props: IProps & IMappedProps) => {
  let initialValues = {};
  if (props.formType === "edit") {
    initialValues = userStoryFactoryForm(props.userStoryValues);
  }
  return {
    initialValues,
    formValues:
      props.formType === "creation"
        ? getFormValues("user_story_form")(state)
        : initialValues,
    showLoading: state.specification.showLoading,
    showSuccess: state.specification.showSuccess,
    showFailed: state.specification.showFailed,
    errorMessage: state.specification.errorMessage,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    writeUserStory: (userStoryValues: any) =>
      dispatch(writeUserStoryAction(userStoryValues, formConfiguration.form)),
    hideSuccessMessage: () => dispatch(hideSuccessAlert("USER_STORY")),
    hideErrorMessage: () => dispatch(hideErrorAlert("USER_STORY")),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(UserStoryForm));
