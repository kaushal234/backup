import { postTocWithDummyData } from "../api/postToc";
import { formApiAutoCompleteDropdown } from "../common/formApiAutoCompleteDropdown";
import { formField } from "../common/formField";
import { formRadioButtons } from "../common/formRadioButtons";
import { formSingleSelectDropdown } from "../common/formSingleSelectDropdown";
import { API_CALL } from "../constants/api";
import { TEST_USER_SUPER } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkCreateTocSprWithExistingAddress = () => {
  cy.getBySel("toc-spr-create-heading").contains(TEXT.tocSprCreate.heading);

  cy.getBySel("toc-detail-parts-add").click();

  cy.getBySel("toc-spr-part-form-heading-1").contains(
    TEXT.tocSprCreate.parts.part1.title
  );

  formApiAutoCompleteDropdown({
    fieldName: "toc-spr-part-form-part-number-1",
    checkLabel: TEXT.tocSprCreate.parts.part1.partNumber.title,
    requiredError: TEXT.tocSprCreate.parts.part1.partNumber.error,
    selectValue: TEXT.tocSprCreate.parts.part1.partNumber.value,
  });

  formField({
    fieldName: "toc-spr-part-form-quantity-1",
    checkLabel: TEXT.tocSprCreate.parts.part1.quantity.title,
    checkValue: TEXT.tocSprCreate.parts.part1.quantity.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocSprCreate.parts.part1.quantity.error,
    setValue: TEXT.tocSprCreate.parts.part1.quantity.value,
  });

  formField({
    fieldName: "toc-spr-part-form-uom-1",
    checkLabel: TEXT.tocSprCreate.parts.part1.uom.title,
    checkValue: TEXT.tocSprCreate.parts.part1.uom.autoFilledValue,
    clear: true,
    requiredError: TEXT.tocSprCreate.parts.part1.uom.error,
    setValue: TEXT.tocSprCreate.parts.part1.uom.value,
  });

  formField({
    fieldName: "toc-spr-part-form-comment-1",
    checkLabel: TEXT.tocSprCreate.parts.part1.comment.title,
    setValue: TEXT.tocSprCreate.parts.part1.comment.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-spr-part-form-part-number-2",
    selectValue: TEXT.tocSprCreate.parts.part2.partNumber.value,
  });

  formField({
    fieldName: "toc-spr-part-form-quantity-2",
    setValue: TEXT.tocSprCreate.parts.part2.quantity.value,
  });

  formField({
    fieldName: "toc-spr-part-form-uom-2",
    setValue: TEXT.tocSprCreate.parts.part2.uom.value,
  });

  formField({
    fieldName: "toc-spr-part-form-comment-2",
    setValue: TEXT.tocSprCreate.parts.part2.comment.value,
  });

  cy.getBySel("toc-detail-parts-add").click();

  cy.getBySel("toc-spr-part-form-delete-3").click();

  cy.getBySel("toc-spr-form-address-heading").contains(
    TEXT.tocSprCreate.address.title
  );

  formSingleSelectDropdown({
    fieldName: "toc-spr-part-form-type",
    checkLabel: TEXT.tocSprCreate.address.existing.type.title,
    selectValue: TEXT.tocSprCreate.address.existing.type.value,
  });

  formRadioButtons({
    fieldName: "toc-spr-part-form-delivery-address",
    checkLabel: TEXT.tocSprCreate.address.existing.address.title,
    selectIndex: TEXT.tocSprCreate.address.existing.address.value,
  });

  formField({
    fieldName: "toc-spr-part-form-delivery-notes",
    checkLabel: TEXT.tocSprCreate.address.existing.deliveryNotes.title,
    setValue: TEXT.tocSprCreate.address.existing.deliveryNotes.value,
    isTextArea: true,
  });

  cy.getBySel("toc-spr-part-form-submit").click();

  cy.waitForApiWithLoader(API_CALL.postTocSpr, 201);

  cy.url().should("match", TEXT.tocPartsCreate.detailLink);
};

const checkCreateTocSprWithNewAddress = () => {
  cy.get("@tocId").then((tocId) => {
    cy.visit(`/toc/details/${tocId}/spr/new`);
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-spr-part-form-part-number-1",
    selectValue: TEXT.tocSprCreate.parts.part3.partNumber.value,
  });

  formField({
    fieldName: "toc-spr-part-form-quantity-1",
    setValue: TEXT.tocSprCreate.parts.part3.quantity.value,
  });

  formField({
    fieldName: "toc-spr-part-form-uom-1",
    setValue: TEXT.tocSprCreate.parts.part3.uom.value,
  });

  formField({
    fieldName: "toc-spr-part-form-comment-1",
    setValue: TEXT.tocSprCreate.parts.part3.comment.value,
  });

  formSingleSelectDropdown({
    fieldName: "toc-spr-part-form-type",
    selectValue: TEXT.tocSprCreate.address.new.type.value,
  });

  formSingleSelectDropdown({
    fieldName: "toc-spr-part-form-econtact",
    checkLabel: TEXT.tocSprCreate.address.new.eContact.title,
    selectIndex: 0,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-spr-part-form-airport",
    checkLabel: TEXT.tocSprCreate.address.new.airport.title,
    selectValue: TEXT.tocSprCreate.address.new.airport.value,
  });

  formField({
    fieldName: "toc-spr-part-form-lastname",
    checkLabel: TEXT.tocSprCreate.address.new.lastname.title,
    requiredError: TEXT.tocSprCreate.address.new.lastname.error,
    setValue: TEXT.tocSprCreate.address.new.lastname.value,
  });

  formField({
    fieldName: "toc-spr-part-form-firstname",
    checkLabel: TEXT.tocSprCreate.address.new.firstname.title,
    requiredError: TEXT.tocSprCreate.address.new.firstname.error,
    setValue: TEXT.tocSprCreate.address.new.firstname.value,
  });

  formField({
    fieldName: "toc-spr-part-form-company",
    checkLabel: TEXT.tocSprCreate.address.new.company.title,
    requiredError: TEXT.tocSprCreate.address.new.company.error,
    setValue: TEXT.tocSprCreate.address.new.company.value,
  });

  formField({
    fieldName: "toc-spr-part-form-street-1",
    checkLabel: TEXT.tocSprCreate.address.new.street1.title,
    requiredError: TEXT.tocSprCreate.address.new.street1.error,
    setValue: TEXT.tocSprCreate.address.new.street1.value,
  });

  formField({
    fieldName: "toc-spr-part-form-street-2",
    checkLabel: TEXT.tocSprCreate.address.new.street2.title,
    setValue: TEXT.tocSprCreate.address.new.street2.value,
  });

  formField({
    fieldName: "toc-spr-part-form-telephone",
    checkLabel: TEXT.tocSprCreate.address.new.telephone.title,
    requiredError: TEXT.tocSprCreate.address.new.telephone.error,
    setValue: TEXT.tocSprCreate.address.new.telephone.value,
  });

  formField({
    fieldName: "toc-spr-part-form-postal-code",
    checkLabel: TEXT.tocSprCreate.address.new.postalCode.title,
    requiredError: TEXT.tocSprCreate.address.new.postalCode.error,
    setValue: TEXT.tocSprCreate.address.new.postalCode.value,
  });

  formField({
    fieldName: "toc-spr-part-form-town",
    checkLabel: TEXT.tocSprCreate.address.new.town.title,
    setValue: TEXT.tocSprCreate.address.new.town.value,
  });

  formField({
    fieldName: "toc-spr-part-form-city",
    checkLabel: TEXT.tocSprCreate.address.new.city.title,
    requiredError: TEXT.tocSprCreate.address.new.city.error,
    setValue: TEXT.tocSprCreate.address.new.city.value,
  });

  formField({
    fieldName: "toc-spr-part-form-state",
    checkLabel: TEXT.tocSprCreate.address.new.state.title,
    setValue: TEXT.tocSprCreate.address.new.state.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-spr-part-form-country",
    checkLabel: TEXT.tocSprCreate.address.new.country.title,
    requiredError: TEXT.tocSprCreate.address.new.country.error,
    selectValue: TEXT.tocSprCreate.address.new.country.value,
  });

  formField({
    fieldName: "toc-spr-part-form-delivery-notes",
    setValue: TEXT.tocSprCreate.address.new.deliveryNotes.value,
    isTextArea: true,
  });

  cy.getBySel("toc-spr-part-form-submit").click();

  cy.waitForApiWithLoader(API_CALL.postTocSpr, 201);

  cy.url().should("match", TEXT.tocPartsCreate.detailLink);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.postTocSpr);
  cy.visit("/");
  cy.loginWithUser(TEST_USER_SUPER.username, TEST_USER_SUPER.password);
  cy.window().then((window) => {
    postTocWithDummyData(window);
  });
  cy.get("@tocId").then((tocId) => {
    cy.visit(`/toc/details/${tocId}/spr/new`);
  });
};

describe("TOC Parts Create Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkCreateTocSprWithExistingAddress();
    checkCreateTocSprWithNewAddress();
  });
});
