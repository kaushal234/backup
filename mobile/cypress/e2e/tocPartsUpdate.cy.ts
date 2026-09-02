import { postTocWithDummyData } from "../api/postToc";
import { postTocPart } from "../api/postTocPart";
import { formField } from "../common/formField";
import { formSingleSelectDropdown } from "../common/formSingleSelectDropdown";
import { API_CALL } from "../constants/api";
import { TEST_USER_CSM } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkUpdateTocPart = () => {
  cy.getBySel("toc-parts-update-heading").contains(TEXT.tocPartsUpdate.heading);

  formField({
    fieldName: "toc-parts-form-part-number",
    checkLabel: TEXT.tocPartsUpdate.partNumber.title,
    checkValue: TEXT.tocPartsUpdate.partNumber.autoFilledValue,
    clear: true,
    setValue: TEXT.tocPartsUpdate.partNumber.value,
  });

  formField({
    fieldName: "toc-parts-form-vendor-part-number",
    checkLabel: TEXT.tocPartsUpdate.vendorPartNumber.title,
    checkValue: TEXT.tocPartsUpdate.vendorPartNumber.autoFilledValue,
    clear: true,
    setValue: TEXT.tocPartsUpdate.vendorPartNumber.value,
  });

  formField({
    fieldName: "toc-parts-form-quantity",
    checkLabel: TEXT.tocPartsUpdate.quantity.title,
    checkValue: TEXT.tocPartsUpdate.quantity.autoFilledValue,
    clear: true,
    setValue: TEXT.tocPartsUpdate.quantity.value,
  });

  formSingleSelectDropdown({
    fieldName: "toc-parts-form-replacement",
    checkLabel: TEXT.tocPartsUpdate.replacement.title,
    checkValue: TEXT.tocPartsUpdate.replacement.value,
    clear: true,
    selectIndex: 1,
  });

  formField({
    fieldName: "toc-parts-form-description",
    checkLabel: TEXT.tocPartsUpdate.description.title,
    checkValue: TEXT.tocPartsUpdate.description.autoFilledValue,
    clear: true,
    setValue: TEXT.tocPartsUpdate.description.value,
    isTextArea: true,
  });

  formField({
    fieldName: "toc-parts-form-comment",
    checkLabel: TEXT.tocPartsUpdate.comment.title,
    checkValue: TEXT.tocPartsUpdate.comment.autoFilledValue,
    clear: true,
    setValue: TEXT.tocPartsUpdate.comment.value,
    isTextArea: true,
  });

  cy.getBySel("toc-form-submit").click();

  cy.waitForApiWithLoader(API_CALL.putTocPart, 200);

  cy.url().should("match", TEXT.tocPartsUpdate.detailLink);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.putTocPart);
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password);
  cy.window().then((window) => {
    postTocWithDummyData(window);
  });
  cy.get("@tocId").then((tocId) => {
    cy.window().then((window) => {
      postTocPart(window, tocId);
    });
    cy.get("@tocPartId").then((tocPartId) => {
      cy.visit(`/toc/details/${tocId}/parts/edit/${tocPartId}`);
    });
  });
};

describe("TOC Parts Update Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkUpdateTocPart();
  });
});
