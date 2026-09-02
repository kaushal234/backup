import { formApiAutoCompleteDropdown } from "../common/formApiAutoCompleteDropdown";
import { formDatePicker } from "../common/formDatePicker";
import { formFileUpload } from "../common/formFileUpload";
import { formField } from "../common/formField";
import { formSwitch } from "../common/formSwitch";
import { formMultiSelectDropdown } from "../common/formMultiSelectDropdown";
import { checkRichTextArea } from "../common/formRichTextField";
import { formSingleSelectDropdown } from "../common/formSingleSelectDropdown";
import { API_CALL } from "../constants/api";
import { FILE_PATH, TEST_USER_SUPER } from "../constants/constants";
import { TEXT } from "../constants/strings";
import { formHourMeter } from "../common/formHourMeter";

const checkDetailsForm = () => {
  cy.getBySel("fab-icon").click();

  cy.getBySel("toc-create-heading").contains(TEXT.tocCreate.heading);

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-equipment-record",
    checkLabel: TEXT.tocCreate.equipmentRecord.title,
    selectValue: TEXT.tocCreate.equipmentRecord.value,
    requiredError: TEXT.tocCreate.equipmentRecord.error,
  });

  formField({
    fieldName: "toc-form-serial-number",
    checkLabel: TEXT.tocCreate.serialNumber.title,
    setValue: TEXT.tocCreate.serialNumber.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-airport",
    checkLabel: TEXT.tocCreate.airport.title,
    checkValue: TEXT.tocCreate.airport.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocCreate.airport.error,
    selectValue: TEXT.tocCreate.airport.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-service-organization",
    checkLabel: TEXT.tocCreate.serviceOrganisation.title,
    checkValue: TEXT.tocCreate.serviceOrganisation.value,
    clear: true,
    requiredError: TEXT.tocCreate.serviceOrganisation.error,
    selectValue: "location_sso",
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-customer",
    checkLabel: TEXT.tocCreate.customer.title,
    checkValue: TEXT.tocCreate.customer.autoFilledValue,
    clear: true,
    selectValue: TEXT.tocCreate.customer.value,
    requiredError: TEXT.tocCreate.customer.error,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-assignee",
    checkLabel: TEXT.tocCreate.assignee.title,
    checkValue: TEXT.tocCreate.assignee.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocCreate.assignee.error,
    selectValue: TEXT.tocCreate.assignee.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-technician",
    checkLabel: TEXT.tocCreate.technician.title,
    selectValue: TEXT.tocCreate.technician.value,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-ifactor",
    checkLabel: TEXT.tocCreate.ifactor.title,
    requiredError: TEXT.tocCreate.ifactor.error.required,
    selectIndex: 0,
  });

  formField({
    fieldName: "toc-form-error-codes",
    checkLabel: TEXT.tocCreate.errorCodes.title,
    setValue: TEXT.tocCreate.errorCodes.value,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-payer",
    checkLabel: TEXT.tocCreate.payer.title,
    requiredError: TEXT.tocCreate.payer.error,
    selectIndex: 0,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-service-activity",
    checkLabel: TEXT.tocCreate.serviceActivity.title,
    requiredError: TEXT.tocCreate.serviceActivity.error,
    selectIndex: 2,
  });

  formSwitch({
    fieldName: "toc-form-confidential",
    checkLabel: TEXT.tocCreate.confidential.title,
    toggle: true,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-unit-operational-status",
    selectIndex: 1,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-ifactor",
    valueError: TEXT.tocCreate.ifactor.error.mcf,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-unit-operational-status",
    checkLabel: TEXT.tocCreate.unitOperationalStatus.title,
    requiredError: TEXT.tocCreate.unitOperationalStatus.error,
    selectIndex: 0,
  });

  formMultiSelectDropdown({
    fieldName: "toc-form-tags",
    checkLabel: TEXT.tocCreate.tags.title,
    selectIndex: [0],
  });

  formHourMeter({
    fieldName: "toc-form-hour-meter",
    checkLabel: TEXT.tocCreate.hourMeter.title,
    updateValue: true,
  });

  formField({
    fieldName: "toc-form-third-party-name",
    checkLabel: TEXT.tocCreate.thirdPartyName.title,
    setValue: TEXT.tocCreate.thirdPartyName.value,
  });

  formField({
    fieldName: "toc-form-third-party-ref",
    checkLabel: TEXT.tocCreate.thirdPartyRef.title,
    setValue: TEXT.tocCreate.thirdPartyRef.value,
  });

  formField({
    fieldName: "toc-form-title",
    checkLabel: TEXT.tocCreate.title.title,
    requiredError: TEXT.tocCreate.title.error,
    setValue: TEXT.tocCreate.title.value,
  });

  cy.getBySel("toc-form-description-label").contains(
    TEXT.tocCreate.description.title
  );
  checkRichTextArea(
    "toc-form-description-field",
    TEXT.tocCreate.description.firstDescription,
    TEXT.tocCreate.description.secondDescription,
    TEXT.tocCreate.description.error
  );

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

  formSwitch({
    fieldName: "toc-form-switch",
    checkLabel: TEXT.tocCreate.switch.title,
    toggle: true,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-service-technician",
    checkLabel: TEXT.tocCreate.serviceTechnician.title,
    checkValue: TEXT.tocCreate.serviceTechnician.autoFilledValue,
    selectValue: TEXT.tocCreate.serviceTechnician.value,
  });

  formDatePicker({
    fieldName: "toc-form-planned-date",
    checkLabel: TEXT.tocCreate.plannedDate.title,
    setValue: TEXT.tocCreate.plannedDate.value,
  });

  formSingleSelectDropdown({
    fieldName: "toc-form-main-contact",
    checkLabel: TEXT.tocCreate.mainContact.title,
    selectIndex: 1,
  });

  formMultiSelectDropdown({
    fieldName: "toc-form-contacts",
    checkLabel: TEXT.tocCreate.additionalContacts.title,
    checkValue: TEXT.tocCreate.additionalContacts.autoFilledValue,
    clear: true,
    selectIndex: [0],
  });

  cy.getBySel("toc-form-submit").click();
};

const checkFilesForm = () => {
  formFileUpload({
    fieldName: "toc-file-form-main-file",
    checkLabel: TEXT.tocCreate.mainFile.title,
    setFiles: [{ file: FILE_PATH.sample1 }],
  });

  formFileUpload({
    fieldName: "toc-file-form-regular-file",
    checkLabel: TEXT.tocCreate.regularFile.title,
    setFiles: [{ file: FILE_PATH.sample2 }],
  });

  cy.getBySel("toc-file-form-submit").click();

  cy.waitForApiWithLoader(API_CALL.postToc, 201);

  cy.url().should("match", TEXT.tocCreate.tocLink);
};

const checkAddContact = () => {
  cy.visit("/toc/new");

  formApiAutoCompleteDropdown({
    fieldName: "toc-form-customer",
    selectValue: TEXT.tocCreate.customer.value,
  });

  cy.getBySel("toc-new-contact-button").click();

  formSingleSelectDropdown({
    fieldName: "contact-popup-crt",
    checkLabel: TEXT.tocCreate.newCustomer.crt.title,
    requiredError: TEXT.tocCreate.newCustomer.crt.error,
    selectValue: TEXT.tocCreate.newCustomer.crt.value,
  });

  formField({
    fieldName: "contact-popup-email",
    checkLabel: TEXT.tocCreate.newCustomer.email.title,
    requiredError: TEXT.tocCreate.newCustomer.email.error,
    setValue: Date.now() + TEXT.tocCreate.newCustomer.email.value,
  });

  formField({
    fieldName: "contact-popup-last-name",
    checkLabel: TEXT.tocCreate.newCustomer.lastname.title,
    requiredError: TEXT.tocCreate.newCustomer.lastname.error,
    setValue: TEXT.tocCreate.newCustomer.lastname.value,
  });

  formField({
    fieldName: "contact-popup-first-name",
    checkLabel: TEXT.tocCreate.newCustomer.firstname.title,
    requiredError: TEXT.tocCreate.newCustomer.firstname.error,
    setValue: TEXT.tocCreate.newCustomer.firstname.value,
  });

  formField({
    fieldName: "contact-popup-division",
    checkLabel: TEXT.tocCreate.newCustomer.division.title,
    requiredError: TEXT.tocCreate.newCustomer.division.error,
    setValue: TEXT.tocCreate.newCustomer.division.value,
  });

  formField({
    fieldName: "contact-popup-department",
    checkLabel: TEXT.tocCreate.newCustomer.department.title,
    requiredError: TEXT.tocCreate.newCustomer.department.error,
    setValue: TEXT.tocCreate.newCustomer.department.value,
  });

  formField({
    fieldName: "contact-popup-job-title",
    checkLabel: TEXT.tocCreate.newCustomer.jobTitle.title,
    requiredError: TEXT.tocCreate.newCustomer.jobTitle.error,
    setValue: TEXT.tocCreate.newCustomer.jobTitle.value,
  });

  formField({
    fieldName: "contact-popup-phone-number",
    checkLabel: TEXT.tocCreate.newCustomer.phoneNumber.title,
    requiredError: TEXT.tocCreate.newCustomer.phoneNumber.error,
    setValue: TEXT.tocCreate.newCustomer.phoneNumber.value,
  });

  formSingleSelectDropdown({
    fieldName: "contact-popup-language",
    checkLabel: TEXT.tocCreate.newCustomer.language.title,
    selectValue: TEXT.tocCreate.newCustomer.language.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "contact-popup-country",
    checkLabel: TEXT.tocCreate.newCustomer.country.title,
    selectValue: TEXT.tocCreate.newCustomer.country.value,
  });

  cy.getBySel("contact-popup-submit").click();

  cy.waitForApiWithLoader(API_CALL.postExtranetUser, 201);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.postToc);
  cy.interceptApi(API_CALL.postExtranetUser);
  cy.visit("/");
  cy.loginWithUser(TEST_USER_SUPER.username, TEST_USER_SUPER.password);
  cy.visit("/toc");
};

describe("TOC Create Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkDetailsForm();
    checkFilesForm();
    checkAddContact();
  });
});
