import { postTocWithDummyData } from "../api/postToc";
import { formField } from "../common/formField";
import { formSingleSelectDropdown } from "../common/formSingleSelectDropdown";
import { TEST_USER_CSM } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkTocStatusFlow = () => {
  cy.getBySel("toc-status-progress-status").contains(
    TEXT.tocDetail.status.type.inProgress
  );

  cy.getBySel("toc-status-progress-edit").click();

  formSingleSelectDropdown({
    fieldName: "toc-status-popup-status",
    selectValue: TEXT.tocDetail.status.type.suspended,
  });
  formField({
    fieldName: "toc-status-popup-reason",
    isTextArea: true,
    checkLabel: TEXT.tocDetail.status.popup.reason.label,
    requiredError: TEXT.tocDetail.status.popup.reason.error,
    setValue: TEXT.tocDetail.status.popup.reason.value,
  });

  cy.getBySel("toc-status-popup-submit").click();

  cy.getBySel("toc-status-progress-status").contains(
    TEXT.tocDetail.status.type.suspended
  );

  cy.getBySel("toc-status-progress-edit").click();

  formSingleSelectDropdown({
    fieldName: "toc-status-popup-status",
    selectValue: TEXT.tocDetail.status.type.inProgress,
  });
  cy.getBySel("toc-status-popup-submit").click();

  cy.getBySel("toc-status-progress-status").contains(
    TEXT.tocDetail.status.type.inProgress
  );

  cy.getBySel("toc-status-progress-edit").click();

  formSingleSelectDropdown({
    fieldName: "toc-status-popup-status",
    selectValue: TEXT.tocDetail.status.type.solved,
  });
  formField({
    fieldName: "toc-status-popup-symptoms",
    checkLabel: TEXT.tocDetail.status.popup.symptoms.label,
    checkValue: TEXT.tocDetail.status.popup.symptoms.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocDetail.status.popup.symptoms.error,
    setValue: TEXT.tocDetail.status.popup.symptoms.value,
  });
  formField({
    fieldName: "toc-status-popup-root-cause",
    checkLabel: TEXT.tocDetail.status.popup.rootCause.label,
    requiredError: TEXT.tocDetail.status.popup.rootCause.error,
    setValue: TEXT.tocDetail.status.popup.rootCause.value,
  });
  formField({
    fieldName: "toc-status-popup-solution",
    checkLabel: TEXT.tocDetail.status.popup.solution.label,
    requiredError: TEXT.tocDetail.status.popup.solution.error,
    setValue: TEXT.tocDetail.status.popup.solution.value,
  });
  formField({
    fieldName: "toc-status-popup-third-party-job-description",
    checkLabel: TEXT.tocDetail.status.popup.thirdPartyJobDescription.label,
    requiredError: TEXT.tocDetail.status.popup.thirdPartyJobDescription.error,
    setValue: TEXT.tocDetail.status.popup.thirdPartyJobDescription.value,
  });
  formField({
    fieldName: "toc-status-popup-third-party-hours",
    checkLabel: TEXT.tocDetail.status.popup.thirdPartyHours.label,
    requiredError: TEXT.tocDetail.status.popup.thirdPartyHours.error,
    setValue: TEXT.tocDetail.status.popup.thirdPartyHours.value,
  });
  cy.getBySel("toc-status-popup-submit").click();

  cy.getBySel("toc-status-progress-status").contains(
    TEXT.tocDetail.status.type.solved
  );

  cy.getBySel("toc-status-progress-edit").click();

  formSingleSelectDropdown({
    fieldName: "toc-status-popup-status",
    selectValue: TEXT.tocDetail.status.type.inProgress,
  });

  cy.getBySel("toc-status-popup-submit").click();

  cy.getBySel("toc-status-progress-status").contains(
    TEXT.tocDetail.status.type.inProgress
  );

  cy.getBySel("toc-status-progress-edit").click();

  formSingleSelectDropdown({
    fieldName: "toc-status-popup-status",
    selectValue: TEXT.tocDetail.status.type.solved,
  });
  formField({
    fieldName: "toc-status-popup-symptoms",
    setValue: TEXT.tocDetail.status.popup.symptoms.value,
  });
  formField({
    fieldName: "toc-status-popup-root-cause",
    setValue: TEXT.tocDetail.status.popup.rootCause.value,
  });
  formField({
    fieldName: "toc-status-popup-solution",
    setValue: TEXT.tocDetail.status.popup.solution.value,
  });
  formField({
    fieldName: "toc-status-popup-third-party-job-description",
    checkValue: TEXT.tocDetail.status.popup.thirdPartyJobDescription.value,
  });
  formField({
    fieldName: "toc-status-popup-third-party-hours",
    checkValue: TEXT.tocDetail.status.popup.thirdPartyHours.value,
  });

  cy.getBySel("toc-status-popup-submit").click();

  cy.getBySel("toc-status-progress-status").contains(
    TEXT.tocDetail.status.type.solved
  );

  cy.getBySel("toc-status-progress-edit").click();

  formSingleSelectDropdown({
    fieldName: "toc-status-popup-status",
    selectValue: TEXT.tocDetail.status.type.closed,
  });

  cy.getBySel("toc-status-popup-submit").click();

  cy.getBySel("toc-status-progress-status").contains(
    TEXT.tocDetail.status.type.closed
  );
};

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password);
  cy.window().then((window) => {
    postTocWithDummyData(window);
  });
  cy.get("@tocId").then((tocId) => {
    cy.visit(`/toc/details/${tocId}`);
  });
};

describe("TOC Page", () => {
  it("Status Functionality", () => {
    setup();
    checkTocStatusFlow();
  });
});
