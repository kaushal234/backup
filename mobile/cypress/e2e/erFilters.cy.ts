import { API_CALL } from "../constants/api";
import { TEXT } from "../constants/strings";
import { formApiAutoCompleteDropdown } from "../common/formApiAutoCompleteDropdown";
import { formMultiSelectDropdown } from "../common/formMultiSelectDropdown";

const checkFilters = () => {
  cy.visit("/er/filter");
  cy.getBySel("er-filter-heading").contains(TEXT.erFilters.heading);

  formApiAutoCompleteDropdown({
    fieldName: "er-filter-serial-number",
    checkLabel: TEXT.erFilters.serialNumber.title,
    selectValue: TEXT.erFilters.serialNumber.value,
  });
  cy.waitForApiWithLoader(API_CALL.getErList, 200, null, true);
  cy.waitForApiWithLoader(API_CALL.getErList, 200, null, true);

  formApiAutoCompleteDropdown({
    fieldName: "er-filter-airport",
    checkLabel: TEXT.erFilters.airport.title,
    selectValue: TEXT.erFilters.airport.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "er-filter-equipment-type",
    checkLabel: TEXT.erFilters.equipmentType.title,
    selectValue: TEXT.erFilters.equipmentType.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "er-filter-models",
    checkLabel: TEXT.erFilters.models.title,
    selectValue: TEXT.erFilters.models.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "er-filter-buyer",
    checkLabel: TEXT.erFilters.buyer.title,
    selectValue: TEXT.erFilters.buyer.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "er-filter-end-user",
    checkLabel: TEXT.erFilters.endUser.title,
    selectValue: TEXT.erFilters.endUser.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "er-filter-maintainer",
    checkLabel: TEXT.erFilters.maintainer.title,
    selectValue: TEXT.erFilters.maintainer.value,
  });

  cy.getBySel("er-filter-submit").click();

  const params: { [key: string]: string | RegExp } = {
    serialNumber: "green tag",
    "product.family.productType[]": /\/sales\/product_types\/\d+/,
    "product[]": /\/sales\/products\/\d+/,
    airport: /\/airports\/\d+/,
    buyer: /\/sales\/customers\/\d+/,
    endUser: /\/sales\/customers\/\d+/,
    maintainer: /\/sales\/customers\/\d+/,
  };
  cy.waitForApiWithLoader(API_CALL.getErList, 200, params);

  cy.getBySel("er-list-filter-icon").contains(TEXT.erFilters.filterCount);
  cy.getBySel("er-list-filter-icon").click();
  cy.url().should("include", TEXT.erFilters.filterUrl);

  for (let i = 0; i < +TEXT.erFilters.clearFilterCount; i++) {
    cy.get(
      "[data-testid^=CancelIcon], [data-testid^=ClearIcon], [data-testid^=CloseIcon]"
    )
      .first()
      .click({ force: true });
  }

  cy.getBySel("er-filter-submit").click();
  cy.waitForApiWithLoader(API_CALL.getErList, 200);
  cy.getBySel("er-card").should("have.length", 5);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.getErList);
  cy.visit("/");
  cy.loginWithUser();
};

describe("ER Filter Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkFilters();
  });
});
