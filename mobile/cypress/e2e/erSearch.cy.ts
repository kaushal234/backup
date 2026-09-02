import { formField } from "../common/formField";
import { TEXT } from "../constants/strings";

const checkSearch = () => {
  cy.visit("/er/finder");
  formField({
    fieldName: "er-search",
    requiredError: TEXT.erSearch.search.error,
    setValue: TEXT.erSearch.search.serialNumber,
  });
  cy.getBySel("er-search-submit").click();
  cy.url().should("include", TEXT.erSearch.search.redirectUrl);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
  cy.loginWithUser();
};

describe("Scan ER Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkSearch();
  });
});
