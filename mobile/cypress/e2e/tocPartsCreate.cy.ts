import { postTocWithDummyData } from "../api/postToc";
import { formField } from "../common/formField";
import { formSingleSelectDropdown } from "../common/formSingleSelectDropdown";
import { API_CALL } from "../constants/api";
import { TEST_USER_CSM } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkCreateTocPart = () => {
  cy.getBySel("toc-parts-create-heading").contains(TEXT.tocPartsCreate.heading);

  formField({
    fieldName: "toc-parts-form-part-number",
    checkLabel: TEXT.tocPartsCreate.partNumber.title,
    requiredError: TEXT.tocPartsCreate.partNumber.error,
    setValue: TEXT.tocPartsCreate.partNumber.value,
  });

  formField({
    fieldName: "toc-parts-form-vendor-part-number",
    checkLabel: TEXT.tocPartsCreate.vendorPartNumber.title,
    setValue: TEXT.tocPartsCreate.vendorPartNumber.value,
  });

  formField({
    fieldName: "toc-parts-form-quantity",
    checkLabel: TEXT.tocPartsCreate.quantity.title,
    setValue: TEXT.tocPartsCreate.quantity.value,
  });

  formSingleSelectDropdown({
    fieldName: "toc-parts-form-replacement",
    checkLabel: TEXT.tocPartsCreate.replacement.title,
    selectIndex: 0,
  });

  formField({
    fieldName: "toc-parts-form-description",
    checkLabel: TEXT.tocPartsCreate.description.title,
    requiredError: TEXT.tocPartsCreate.description.error,
    setValue: TEXT.tocPartsCreate.description.value,
    isTextArea: true,
  });

  formField({
    fieldName: "toc-parts-form-comment",
    checkLabel: TEXT.tocPartsCreate.comment.title,
    setValue: TEXT.tocPartsCreate.comment.value,
    isTextArea: true,
  });

  cy.getBySel("toc-form-submit").click();

  cy.waitForApiWithLoader(API_CALL.postTocPart, 201);

  cy.url().should("match", TEXT.tocPartsCreate.detailLink);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.postTocPart);
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password);
  cy.window().then((window) => {
    postTocWithDummyData(window);
  });
  cy.get("@tocId").then((tocId) => {
    cy.visit(`/toc/details/${tocId}/parts/new`);
  });
};

describe("TOC Parts Create Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkCreateTocPart();
  });
});
