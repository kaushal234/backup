import dayjs from "dayjs";
import { API_CALL } from "../constants/api";
import { TEXT } from "../constants/strings";
import { checkDateRange } from "../common/dateRange";
import { formatDate } from "../../src/utils/date";
import { formApiAutoCompleteDropdown } from "../common/formApiAutoCompleteDropdown";
import { formSingleSelectDropdown } from "../common/formSingleSelectDropdown";
import { formMultiSelectDropdown } from "../common/formMultiSelectDropdown";
import { formField } from "../common/formField";
import { REGEX } from "../constants/regExp";

const checkFilters = () => {
  cy.visit("/csr/filter");
  formField({
    fieldName: "csr-search",
    setValue: "2",
    requiredError: TEXT.csrFilters.search.error,
  });
  cy.getBySel("csr-search-submit").click();
  cy.url().should("include", TEXT.csrFilters.search.redirectUrl);

  cy.visit("/csr/filter");
  cy.getBySel("csr-filter-heading").contains(TEXT.csrFilters.heading);

  formApiAutoCompleteDropdown({
    fieldName: "csr-filter-service-organisation",
    checkLabel: TEXT.csrFilters.serviceOrganisation.title,
    selectValue: TEXT.csrFilters.serviceOrganisation.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "csr-filter-airport",
    checkLabel: TEXT.csrFilters.airport.title,
    selectValue: TEXT.csrFilters.airport.value,
  });

  formMultiSelectDropdown({
    fieldName: "csr-filter-status",
    checkLabel: TEXT.csrFilters.status.title,
    selectIndex: [0],
  });

  formApiAutoCompleteDropdown({
    fieldName: "csr-filter-serial-number",
    checkLabel: TEXT.csrFilters.serialNumber.title,
    selectValue: TEXT.csrFilters.serialNumber.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "csr-filter-equipment-type",
    checkLabel: TEXT.csrFilters.equipmentType.title,
    selectValue: TEXT.csrFilters.equipmentType.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "csr-filter-models",
    checkLabel: TEXT.csrFilters.models.title,
    selectValue: TEXT.csrFilters.models.value,
  });

  formSingleSelectDropdown({
    fieldName: "csr-filter-sales-organisation",
    checkLabel: TEXT.csrFilters.salesOrganization.title,
    selectIndex: 0,
  });

  formSingleSelectDropdown({
    fieldName: "csr-filter-manufacturer-location",
    checkLabel: TEXT.csrFilters.manufacturerLocation.title,
    selectIndex: 0,
  });

  formApiAutoCompleteDropdown({
    fieldName: "csr-filter-created-by",
    checkLabel: TEXT.csrFilters.createdBy.title,
    selectValue: TEXT.csrFilters.createdBy.value,
  });

  cy.getBySel("csr-filter-created-after-label").contains(
    TEXT.csrFilters.createdAfter.title
  );
  cy.getBySel("csr-filter-created-before-label").contains(
    TEXT.csrFilters.createdBefore.title
  );
  checkDateRange(
    "csr-filter-created-after",
    "csr-filter-created-before",
    TEXT.csrFilters.createdAfter.error
  );

  formApiAutoCompleteDropdown({
    fieldName: "csr-filter-service-technician",
    checkLabel: TEXT.csrFilters.serviceTechnician.title,
    selectValue: TEXT.csrFilters.serviceTechnician.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "csr-filter-end-user",
    checkLabel: TEXT.csrFilters.endUser.title,
    selectValue: TEXT.csrFilters.endUser.value,
  });

  cy.getBySel("csr-filter-completed-after-label").contains(
    TEXT.csrFilters.completedAfter.title
  );
  cy.getBySel("csr-filter-completed-before-label").contains(
    TEXT.csrFilters.completedBefore.title
  );
  checkDateRange(
    "csr-filter-completed-after",
    "csr-filter-completed-before",
    TEXT.csrFilters.completedAfter.error
  );

  cy.getBySel("csr-filter-closed-after-label").contains(
    TEXT.csrFilters.closedAfter.title
  );
  cy.getBySel("csr-filter-closed-before-label").contains(
    TEXT.csrFilters.closedBefore.title
  );
  checkDateRange(
    "csr-filter-closed-after",
    "csr-filter-closed-before",
    TEXT.csrFilters.closedAfter.error
  );

  formMultiSelectDropdown({
    fieldName: "csr-filter-country",
    checkLabel: TEXT.csrFilters.country.title,
    selectIndex: [0],
  });

  formMultiSelectDropdown({
    fieldName: "csr-filter-csr-type",
    checkLabel: TEXT.csrFilters.csrType.title,
    selectIndex: [0],
  });

  cy.getBySel("csr-filter-submit").click();

  const params: { [key: string]: string | RegExp } = {
    equipmentRecord: /\/equipment_records\/\d+/,
    "status[]": REGEX.word,
    createdBy: /\/people\/\d+/,
    "createdAt[after]": formatDate(dayjs().toISOString()),
    "createdAt[before]": formatDate(dayjs().toISOString()),
    "equipmentRecord.salesOrganisation": /\/locations\/\d+/,
    "equipmentRecord.salesOrganisationService": /\/locations\/\d+/,
    "equipmentRecord.manufacturerLocation": /\/locations\/\d+/,
    "equipmentRecord.product.family.productType[]":
      /\/sales\/product_types\/\d+/,
    "equipmentRecord.product[]": /\/sales\/products\/\d+/,
    airport: /\/airports\/\d+/,
    "interventions.leader": /\/people\/\d+/,
    "equipmentRecord.endUser": /\/sales\/customers\/\d+/,
    "completedAt[after]": formatDate(dayjs().toISOString()),
    "completedAt[before]": formatDate(dayjs().toISOString()),
    "closedAt[after]": formatDate(dayjs().toISOString()),
    "closedAt[before]": formatDate(dayjs().toISOString()),
    "airport.country[]": /\/countries\/\d+/,
    "discriminator[]": "commissioning",
  };
  cy.waitForApiWithLoader(API_CALL.getCsrList, 200, params);

  cy.getBySel("csr-list-filter-icon").contains(TEXT.csrFilters.filterCount);
  cy.getBySel("csr-list-filter-icon").click();
  cy.url().should("include", TEXT.csrFilters.filterUrl);

  for (let i = 0; i < +TEXT.csrFilters.clearFilterCount; i++) {
    cy.get(
      "[data-testid^=CancelIcon], [data-testid^=ClearIcon], [data-testid^=CloseIcon]"
    )
      .first()
      .click({ force: true });
  }

  cy.getBySel("csr-filter-submit").click();
  cy.waitForApiWithLoader(API_CALL.getCsrList, 200);
  cy.getBySel("csr-card").should("have.length", 5);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.getCsrList);
  cy.visit("/");
  cy.loginWithUser();
};

describe("CSR Filter Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkFilters();
  });
});
