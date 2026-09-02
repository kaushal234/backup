import { postTocAndCsrWithDummyData } from "../api/postTocWithCsr";
import { formHourMeter } from "../common/formHourMeter";
import { formApiAutoCompleteDropdown } from "../common/formApiAutoCompleteDropdown";
import { formDatePicker } from "../common/formDatePicker";
import { formField } from "../common/formField";
import { formRichTextField } from "../common/formRichTextField";
import { formSingleSelectDropdown } from "../common/formSingleSelectDropdown";
import { TEST_USER_CSM } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkCsrUpdateForm = () => {
  formApiAutoCompleteDropdown({
    fieldName: "csr-form-airport",
    checkLabel: TEXT.csrUpdate.airport.title,
    checkValue: TEXT.csrUpdate.airport.autoFilledValue,
    clear: true,
    requiredError: TEXT.csrUpdate.airport.error,
    selectValue: TEXT.csrUpdate.airport.value,
  });

  formField({
    fieldName: "csr-form-title",
    checkLabel: TEXT.csrUpdate.title.title,
    checkValue: TEXT.csrUpdate.title.autoFilledValue,
    clear: true,
    requiredError: TEXT.csrUpdate.title.error,
    setValue: TEXT.csrUpdate.title.value,
  });

  formRichTextField({
    fieldName: "csr-form-description",
    checkLabel: TEXT.csrUpdate.description.title,
    clear: 14,
    requiredError: TEXT.csrUpdate.description.error,
    setValue: TEXT.csrUpdate.description.value,
  });

  formSingleSelectDropdown({
    fieldName: "csr-form-service-technician",
    checkLabel: TEXT.csrUpdate.serviceTechnician.title,
    selectIndex: 0,
  });

  formDatePicker({
    fieldName: "csr-form-planned-date",
    checkLabel: TEXT.csrUpdate.plannedDate.title,
    setValue: TEXT.csrUpdate.plannedDate.value,
  });

  cy.getBySel("csr-form-submit").click();
};

const checkCsrStatusFlow = () => {
  cy.getBySel("csr-status-progress-status").contains(
    TEXT.csrDetail.status.type.assigned
  );

  cy.getBySel("intervention-button").click();

  cy.getBySel("intervention-popup-heading").contains(
    TEXT.csrDetail.intervention.heading
  );

  formSingleSelectDropdown({
    fieldName: "intervention-status",
    checkLabel: TEXT.csrDetail.intervention.status.title,
    requiredError: TEXT.csrDetail.intervention.status.error,
    selectValue: TEXT.csrDetail.intervention.status.values.toContine,
  });

  formDatePicker({
    fieldName: "intervention-start-date",
    checkLabel: TEXT.csrDetail.intervention.startDate.title,
    requiredError: TEXT.csrDetail.intervention.startDate.error,
    setValue: TEXT.csrDetail.intervention.startDate.value,
  });

  formDatePicker({
    fieldName: "intervention-end-date",
    checkLabel: TEXT.csrDetail.intervention.endDate.title,
    requiredError: TEXT.csrDetail.intervention.endDate.error,
    setValue: TEXT.csrDetail.intervention.endDate.value,
  });

  formHourMeter({
    fieldName: "intervention-hour-meter",
    checkLabel: TEXT.csrDetail.intervention.hourMeter.title,
    updateValue: true,
  });

  cy.getBySel("intervention-popup-submit").click();

  cy.getBySel("csr-status-progress-status").contains(
    TEXT.csrDetail.status.type.pending
  );

  cy.getBySel("fab-icon").click({ force: true });

  checkCsrUpdateForm();

  cy.getBySel("intervention-button").click();

  formSingleSelectDropdown({
    fieldName: "intervention-status",
    selectValue: TEXT.csrDetail.intervention.status.values.started,
  });

  formDatePicker({
    fieldName: "intervention-start-date",
    setValue: TEXT.csrDetail.intervention.startDate.value,
  });

  formHourMeter({
    fieldName: "intervention-hour-meter",
    checkLabel: TEXT.csrDetail.intervention.hourMeter.title,
    updateValue: true,
  });

  cy.getBySel("intervention-popup-submit").click();

  cy.getBySel("csr-status-progress-status").contains(
    TEXT.csrDetail.status.type.inProgress
  );

  cy.getBySel("intervention-button").click();

  formSingleSelectDropdown({
    fieldName: "intervention-status",
    selectValue: TEXT.csrDetail.intervention.status.values.solved,
  });

  formDatePicker({
    fieldName: "intervention-end-date",
    setValue: TEXT.csrDetail.intervention.endDate.value,
  });

  formHourMeter({
    fieldName: "intervention-hour-meter",
    checkLabel: TEXT.csrDetail.intervention.hourMeter.title,
    updateValue: true,
  });

  cy.getBySel("intervention-popup-submit").click();

  cy.getBySel("csr-status-progress-status").contains(
    TEXT.csrDetail.status.type.completed
  );

  cy.getBySel("csr-status-progress-edit").click();

  cy.getBySel("csr-status-popup-heading").contains(
    TEXT.csrDetail.status.popup.heading
  );

  formSingleSelectDropdown({
    fieldName: "csr-status-popup-status",
    checkLabel: TEXT.csrDetail.status.popup.status.title,
    requiredError: TEXT.csrDetail.status.popup.status.error,
    selectValue: TEXT.csrDetail.intervention.status.values.closed,
  });

  cy.getBySel("csr-status-popup-submit").click();

  cy.getBySel("csr-status-progress-status").contains(
    TEXT.csrDetail.status.type.closed
  );

  cy.getBySel("fab-icon").click({ force: true });

  formSingleSelectDropdown({
    fieldName: "csr-form-status",
    selectValue: TEXT.csrDetail.intervention.status.values.completed,
  });

  cy.getBySel("csr-form-submit").click();

  cy.getBySel("csr-status-progress-status").contains(
    TEXT.csrDetail.status.type.completed
  );
};

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password);
  cy.window().then((window) => {
    postTocAndCsrWithDummyData(window);
  });
  cy.get("@csrId").then((csrId) => {
    cy.visit(`/csr/details/${csrId}`);
  });
};

describe("CSR Page", () => {
  it("Status Functionality", () => {
    setup();
    checkCsrStatusFlow();
  });
});
