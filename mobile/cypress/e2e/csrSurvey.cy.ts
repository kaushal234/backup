import { postCommissioningCsr } from "../api/postCommissioningCsr";
import { formDatePicker } from "../common/formDatePicker";
import { formCheckboxList } from "../common/formCheckboxList";
import { formField } from "../common/formField";
import { formRadioButtons } from "../common/formRadioButtons";
import { formSwitch } from "../common/formSwitch";
import { formSingleSelectDropdown } from "../common/formSingleSelectDropdown";
import { TEST_USER_CSM } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkInterventionOpenPopUp = () => {
  cy.getBySel("csr-status-progress-status").contains(
    TEXT.csrSurvey.csrStatus.assigned
  );

  cy.getBySel("intervention-button").click();

  formSingleSelectDropdown({
    fieldName: "intervention-status",
    checkLabel: TEXT.csrSurvey.openIntervention.status.title,
    requiredError: TEXT.csrSurvey.openIntervention.status.error,
    selectValue: TEXT.csrSurvey.statusOptions.started,
  });

  cy.getBySel("intervention-popup-submit").click();

  formDatePicker({
    fieldName: "intervention-start-date",
    checkLabel: TEXT.csrSurvey.openIntervention.startDate.title,
    requiredError: TEXT.csrSurvey.openIntervention.startDate.error,
    setValue: TEXT.csrSurvey.openIntervention.startDate.value,
  });

  formRadioButtons({
    fieldName: "csr-survey-form-question-1-question",
    checkLabel: TEXT.csrSurvey.openIntervention.question1.title,
    error: TEXT.csrSurvey.openIntervention.question1.error,
    selectIndex: TEXT.csrSurvey.openIntervention.question1.value,
  });

  formField({
    fieldName: "csr-survey-form-question-1-comment",
    checkLabel: TEXT.csrSurvey.openIntervention.comment1.title,
    requiredError: TEXT.csrSurvey.openIntervention.comment1.error,
    setValue: TEXT.csrSurvey.openIntervention.comment1.value,
    isTextArea: true,
  });

  formRadioButtons({
    fieldName: "csr-survey-form-question-2-question",
    checkLabel: TEXT.csrSurvey.openIntervention.question2.title,
    error: TEXT.csrSurvey.openIntervention.question2.error,
    selectIndex: TEXT.csrSurvey.openIntervention.question2.value,
  });

  formField({
    fieldName: "csr-survey-form-question-2-comment",
    checkLabel: TEXT.csrSurvey.openIntervention.comment2.title,
    requiredError: TEXT.csrSurvey.openIntervention.comment2.error,
    setValue: TEXT.csrSurvey.openIntervention.comment2.value,
    isTextArea: true,
  });

  formRadioButtons({
    fieldName: "csr-survey-form-question-3-question",
    checkLabel: TEXT.csrSurvey.openIntervention.question3.title,
    error: TEXT.csrSurvey.openIntervention.question3.error,
    selectIndex: TEXT.csrSurvey.openIntervention.question3.value,
  });

  formField({
    fieldName: "csr-survey-form-question-3-comment",
    checkLabel: TEXT.csrSurvey.openIntervention.comment3.title,
    requiredError: TEXT.csrSurvey.openIntervention.comment3.error,
    setValue: TEXT.csrSurvey.openIntervention.comment3.value,
    isTextArea: true,
  });

  formCheckboxList({
    fieldName: "csr-survey-form-question-4-question",
    checkLabel: TEXT.csrSurvey.openIntervention.question4.title,
    checkOptions: TEXT.csrSurvey.openIntervention.question4.options,
    error: TEXT.csrSurvey.openIntervention.question4.error,
    toggleIndex: TEXT.csrSurvey.openIntervention.question4.value,
  });

  formField({
    fieldName: "csr-survey-form-question-4-comment",
    checkLabel: TEXT.csrSurvey.openIntervention.comment4.title,
    requiredError: TEXT.csrSurvey.openIntervention.comment4.error,
    setValue: TEXT.csrSurvey.openIntervention.comment4.value,
    isTextArea: true,
  });

  formSwitch({
    fieldName: "csr-survey-form-question-5-question",
    checkLabel: TEXT.csrSurvey.openIntervention.question5.title,
    toggle: true,
  });

  formField({
    fieldName: "csr-survey-form-question-5-comment",
    checkLabel: TEXT.csrSurvey.openIntervention.comment5.title,
    setValue: TEXT.csrSurvey.openIntervention.comment5.value,
    isTextArea: true,
  });

  cy.getBySel("intervention-popup-submit").click();

  cy.getBySel("csr-status-progress-status").contains(
    TEXT.csrSurvey.csrStatus.inProgress
  );
};

const checkInterventionClosePopUp = () => {
  cy.getBySel("csr-status-progress-status").contains(
    TEXT.csrSurvey.csrStatus.inProgress
  );

  cy.getBySel("intervention-button").click();

  formSingleSelectDropdown({
    fieldName: "intervention-status",
    selectValue: TEXT.csrSurvey.statusOptions.solved,
  });

  formDatePicker({
    fieldName: "intervention-end-date",
    checkLabel: TEXT.csrSurvey.closeIntervention.endDate.title,
    requiredError: TEXT.csrSurvey.closeIntervention.endDate.error,
    setValue: TEXT.csrSurvey.closeIntervention.endDate.value,
  });

  formRadioButtons({
    fieldName: "csr-survey-form-question-1-question",
    checkValue: TEXT.csrSurvey.closeIntervention.question1.autoFilledValue,
    selectIndex: TEXT.csrSurvey.closeIntervention.question1.value,
  });

  formField({
    fieldName: "csr-survey-form-question-1-comment",
    checkValue: TEXT.csrSurvey.closeIntervention.comment1.autoFilledValue,
    clear: true,
    setValue: TEXT.csrSurvey.closeIntervention.comment1.value,
    isTextArea: true,
  });

  formRadioButtons({
    fieldName: "csr-survey-form-question-2-question",
    checkValue: TEXT.csrSurvey.closeIntervention.question2.autoFilledValue,
    selectIndex: TEXT.csrSurvey.closeIntervention.question2.value,
  });

  formField({
    fieldName: "csr-survey-form-question-2-comment",
    checkValue: TEXT.csrSurvey.closeIntervention.comment2.autoFilledValue,
    clear: true,
    setValue: TEXT.csrSurvey.closeIntervention.comment2.value,
    isTextArea: true,
  });

  formRadioButtons({
    fieldName: "csr-survey-form-question-3-question",
    checkValue: TEXT.csrSurvey.closeIntervention.question3.autoFilledValue,
    selectIndex: TEXT.csrSurvey.closeIntervention.question3.value,
  });

  formField({
    fieldName: "csr-survey-form-question-3-comment",
    checkValue: TEXT.csrSurvey.closeIntervention.comment3.autoFilledValue,
    clear: true,
    setValue: TEXT.csrSurvey.closeIntervention.comment3.value,
    isTextArea: true,
  });

  formCheckboxList({
    fieldName: "csr-survey-form-question-4-question",
    checkedIndex: TEXT.csrSurvey.closeIntervention.question4.autoFilledValue,
    toggleIndex: TEXT.csrSurvey.closeIntervention.question4.value,
  });

  formField({
    fieldName: "csr-survey-form-question-4-comment",
    checkValue: TEXT.csrSurvey.closeIntervention.comment4.autoFilledValue,
    clear: true,
    setValue: TEXT.csrSurvey.closeIntervention.comment4.value,
    isTextArea: true,
  });

  formSwitch({
    fieldName: "csr-survey-form-question-5-question",
    toggle: true,
  });

  formField({
    fieldName: "csr-survey-form-question-5-comment",
    checkValue: TEXT.csrSurvey.closeIntervention.comment5.autoFilledValue,
    clear: true,
    setValue: TEXT.csrSurvey.closeIntervention.comment5.value,
    isTextArea: true,
  });

  cy.getBySel("intervention-popup-submit").click();

  cy.getBySel("csr-status-progress-status").contains(
    TEXT.csrSurvey.csrStatus.completed
  );
};

const checkSurveyPage = () => {
  cy.getBySel("csr-status-progress-status").contains(
    TEXT.csrSurvey.csrStatus.completed
  );

  cy.getBySel("csr-survey").click();

  cy.getBySel("csr-survey-heading").contains(TEXT.csrSurvey.survey.heading);

  formRadioButtons({
    fieldName: "csr-survey-form-question-1-question",
    checkLabel: TEXT.csrSurvey.openIntervention.question1.title,
    checkValue: TEXT.csrSurvey.survey.question1.autoFilledValue,
    selectIndex: TEXT.csrSurvey.survey.question1.value,
  });

  formField({
    fieldName: "csr-survey-form-question-1-comment",
    checkLabel: TEXT.csrSurvey.openIntervention.comment1.title,
    checkValue: TEXT.csrSurvey.survey.comment1.autoFilledValue,
    clear: true,
    requiredError: TEXT.csrSurvey.openIntervention.comment1.error,
    setValue: TEXT.csrSurvey.survey.comment1.value,
    isTextArea: true,
  });

  formRadioButtons({
    fieldName: "csr-survey-form-question-2-question",
    checkLabel: TEXT.csrSurvey.openIntervention.question2.title,
    checkValue: TEXT.csrSurvey.survey.question2.autoFilledValue,
    selectIndex: TEXT.csrSurvey.survey.question2.value,
  });

  formField({
    fieldName: "csr-survey-form-question-2-comment",
    checkLabel: TEXT.csrSurvey.openIntervention.comment2.title,
    checkValue: TEXT.csrSurvey.survey.comment2.autoFilledValue,
    clear: true,
    requiredError: TEXT.csrSurvey.openIntervention.comment2.error,
    setValue: TEXT.csrSurvey.survey.comment2.value,
    isTextArea: true,
  });

  formRadioButtons({
    fieldName: "csr-survey-form-question-3-question",
    checkLabel: TEXT.csrSurvey.openIntervention.question3.title,
    checkValue: TEXT.csrSurvey.survey.question3.autoFilledValue,
    selectIndex: TEXT.csrSurvey.survey.question3.value,
  });

  formField({
    fieldName: "csr-survey-form-question-3-comment",
    checkLabel: TEXT.csrSurvey.openIntervention.comment3.title,
    checkValue: TEXT.csrSurvey.survey.comment3.autoFilledValue,
    clear: true,
    requiredError: TEXT.csrSurvey.openIntervention.comment3.error,
    setValue: TEXT.csrSurvey.survey.comment3.value,
    isTextArea: true,
  });

  formCheckboxList({
    fieldName: "csr-survey-form-question-4-question",
    checkLabel: TEXT.csrSurvey.openIntervention.question4.title,
    checkOptions: TEXT.csrSurvey.openIntervention.question4.options,
    checkedIndex: TEXT.csrSurvey.survey.question4.autoFilledValue,
    toggleIndex: TEXT.csrSurvey.survey.question4.value,
  });

  formField({
    fieldName: "csr-survey-form-question-4-comment",
    checkLabel: TEXT.csrSurvey.openIntervention.comment4.title,
    checkValue: TEXT.csrSurvey.survey.comment4.autoFilledValue,
    clear: true,
    requiredError: TEXT.csrSurvey.openIntervention.comment4.error,
    setValue: TEXT.csrSurvey.survey.comment4.value,
    isTextArea: true,
  });

  formSwitch({
    fieldName: "csr-survey-form-question-5-question",
    toggle: true,
  });

  formField({
    fieldName: "csr-survey-form-question-5-comment",
    checkLabel: TEXT.csrSurvey.openIntervention.comment5.title,
    checkValue: TEXT.csrSurvey.survey.comment5.autoFilledValue,
    clear: true,
    setValue: TEXT.csrSurvey.survey.comment5.value,
    isTextArea: true,
  });

  cy.getBySel("csr-survey-submit").click();

  cy.getBySel("csr-status-progress-status").contains(
    TEXT.csrSurvey.csrStatus.completed
  );
};

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password);
  cy.window().then((window) => {
    postCommissioningCsr(window);
  });
  cy.get("@csrId").then((csrId) => {
    cy.visit(`/csr/details/${csrId}`);
  });
};

describe("CSR Details Page", () => {
  it("Status Functionality", () => {
    setup();
    checkInterventionOpenPopUp();
    checkInterventionClosePopUp();
    checkSurveyPage();
  });
});
