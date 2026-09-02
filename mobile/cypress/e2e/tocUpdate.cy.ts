import { postTocWithDummyData } from "../api/postToc";
import { formApiAutoCompleteDropdown } from "../common/formApiAutoCompleteDropdown";
import { formField } from "../common/formField";
import { formHourMeter } from "../common/formHourMeter";
import { formMultiSelectDropdown } from "../common/formMultiSelectDropdown";
import { formRichTextField } from "../common/formRichTextField";
import { formSingleSelectDropdown } from "../common/formSingleSelectDropdown";
import { formSwitch } from "../common/formSwitch";
import { API_CALL } from "../constants/api";
import { TEST_USER_CSM } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkUpdateForm = () => {
  cy.getBySel("fab-icon").click();

  cy.getBySel("toc-update-heading").contains(TEXT.tocUpdate.heading);

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-equipment-record",
    checkLabel: TEXT.tocUpdate.equipmentRecord.title,
    checkValue: TEXT.tocUpdate.equipmentRecord.autoFilledValue,
  });

  formField({
    fieldName: "toc-form-serial-number",
    checkLabel: TEXT.tocUpdate.serialNumber.title,
    checkValue: TEXT.tocUpdate.serialNumber.autoFilledValue,
    clear: true,
    setValue: TEXT.tocUpdate.serialNumber.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-airport",
    checkLabel: TEXT.tocUpdate.airport.title,
    checkValue: TEXT.tocUpdate.airport.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocUpdate.airport.error,
    selectValue: TEXT.tocUpdate.airport.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-service-organization",
    checkLabel: TEXT.tocUpdate.serviceOrganisation.title,
    checkValue: TEXT.tocUpdate.serviceOrganisation.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocUpdate.serviceOrganisation.error,
    selectValue: TEXT.tocUpdate.serviceOrganisation.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-customer",
    checkLabel: TEXT.tocUpdate.customer.title,
    checkValue: TEXT.tocUpdate.customer.autoFilledValue,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-main-contact",
    checkLabel: TEXT.tocUpdate.mainContact.title,
    checkValue: TEXT.tocUpdate.mainContact.value,
  });

  formMultiSelectDropdown({
    fieldName: "toc-form-contacts",
    checkLabel: TEXT.tocUpdate.additionalContacts.title,
    checkValue: TEXT.tocUpdate.additionalContacts.autoFilledValue,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-customer",
    clear: true,
    requiredError: TEXT.tocUpdate.customer.error,
    selectValue: TEXT.tocUpdate.customer.value,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-main-contact",
    selectIndex: 0,
  });

  formMultiSelectDropdown({
    fieldName: "toc-form-contacts",
    selectIndex: [0],
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-assignee",
    checkLabel: TEXT.tocUpdate.assignee.title,
    checkValue: TEXT.tocUpdate.assignee.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocUpdate.assignee.error,
    selectValue: TEXT.tocUpdate.assignee.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-technician",
    checkLabel: TEXT.tocUpdate.technician.title,
    checkValue: TEXT.tocUpdate.technician.autoFilledValue,
    clear: true,
    selectValue: TEXT.tocUpdate.technician.value,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-ifactor",
    checkLabel: TEXT.tocUpdate.ifactor.title,
    checkValue: TEXT.tocUpdate.ifactor.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocUpdate.ifactor.error.required,
    selectIndex: 0,
  });

  formField({
    fieldName: "toc-form-error-codes",
    checkLabel: TEXT.tocUpdate.errorCodes.title,
    checkValue: TEXT.tocUpdate.errorCodes.autoFilledValue,
    clear: true,
    setValue: TEXT.tocUpdate.errorCodes.value,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-payer",
    checkLabel: TEXT.tocUpdate.payer.title,
    checkValue: TEXT.tocUpdate.payer.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocUpdate.payer.error,
    selectIndex: 0,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-service-activity",
    checkLabel: TEXT.tocUpdate.serviceActivity.title,
    checkValue: TEXT.tocUpdate.serviceActivity.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocUpdate.serviceActivity.error,
    selectIndex: 2,
  });

  formSwitch({
    fieldName: "toc-form-confidential",
    checkLabel: TEXT.tocCreate.confidential.title,
    toggle: true,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-unit-operational-status",
    checkLabel: TEXT.tocUpdate.unitOperationalStatus.title,
    checkValue: TEXT.tocUpdate.unitOperationalStatus.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocUpdate.unitOperationalStatus.error,
    selectIndex: 1,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-ifactor",
    valueError: TEXT.tocUpdate.ifactor.error.mcf,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-unit-operational-status",
    selectIndex: 0,
  });

  formMultiSelectDropdown({
    fieldName: "toc-form-tags",
    checkLabel: TEXT.tocUpdate.tags.title,
    checkValue: TEXT.tocUpdate.tags.autoFilledValue,
    clear: true,
    selectIndex: [2],
  });

  formHourMeter({
    fieldName: "toc-form-hour-meter",
    checkLabel: TEXT.tocUpdate.hourMeter.title,
    updateValue: true,
  });

  formField({
    fieldName: "toc-form-third-party-name",
    checkLabel: TEXT.tocUpdate.thirdPartyName.title,
    checkValue: TEXT.tocUpdate.thirdPartyName.autoFilledValue,
    clear: true,
    setValue: TEXT.tocUpdate.thirdPartyName.value,
  });

  formField({
    fieldName: "toc-form-third-party-ref",
    checkLabel: TEXT.tocUpdate.thirdPartyRef.title,
    checkValue: TEXT.tocUpdate.thirdPartyRef.autoFilledValue,
    clear: true,
    setValue: TEXT.tocUpdate.thirdPartyRef.value,
  });

  formField({
    fieldName: "toc-form-title",
    checkLabel: TEXT.tocUpdate.title.title,
    checkValue: TEXT.tocUpdate.title.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocUpdate.title.error,
    setValue: TEXT.tocUpdate.title.value,
  });

  formRichTextField({
    fieldName: "toc-form-description",
    checkLabel: TEXT.tocUpdate.description.title,
    clear: 14,
    requiredError: TEXT.tocUpdate.description.error,
    setValue: TEXT.tocUpdate.description.value,
  });

  formSwitch({
    fieldName: "toc-form-confidential",
    checkLabel: TEXT.tocCreate.confidential.title,
    toggle: true,
  });

  formField({
    fieldName: "toc-form-reason",
    checkLabel: TEXT.tocCreate.reason.title,
    requiredError: TEXT.tocCreate.reason.error,
    setValue: TEXT.tocCreate.reason.value,
    isTextArea: true,
  });

  cy.getBySel("toc-form-submit").click();

  cy.waitForApiWithLoader(API_CALL.putToc, 200);

  cy.url().should("match", TEXT.tocUpdate.tocLink);

  cy.getBySel("toc-detail-accordion-title").contains(
    TEXT.tocUpdate.title.value
  );
};

const checkDelete = () => {
  cy.get("@tocId").then((tocId) => {
    cy.visit(`/toc/edit/${tocId}`);
  });

  cy.getBySel("toc-delete-icon").click();
  cy.getBySel("confirmation-popup-title").contains(
    TEXT.tocUpdate.delete.confirmationPopup.title
  );
  cy.getBySel("confirmation-popup-description").contains(
    TEXT.tocUpdate.delete.confirmationPopup.description
  );
  cy.getBySel("confirmation-popup-button-no").click();

  cy.getBySel("toc-delete-icon").click();
  cy.getBySel("confirmation-popup-button-yes").click();
  cy.waitForApiWithLoader(API_CALL.deleteToc, 204);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.putToc);
  cy.interceptApi(API_CALL.deleteToc);
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password);
  cy.window().then((window) => {
    postTocWithDummyData(window);
  });
  cy.get("@tocId").then((tocId) => {
    cy.visit(`/toc/details/${tocId}`);
  });
};

describe("TOC Update Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkUpdateForm();
    checkDelete();
  });
});
