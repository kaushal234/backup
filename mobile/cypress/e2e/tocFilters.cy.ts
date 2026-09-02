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
  cy.visit("/toc/filter");
  formField({
    fieldName: "toc-search",
    requiredError: TEXT.tocFilters.search.error,
    setValue: "2",
  });
  cy.getBySel("toc-search-submit").click();
  cy.url().should("include", TEXT.tocFilters.search.redirectUrl);

  cy.visit("/toc/filter");
  cy.getBySel("toc-filter-heading").contains(TEXT.tocFilters.heading);

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-service-organisation",
    checkLabel: TEXT.tocFilters.serviceOrganisation.title,
    selectValue: TEXT.tocFilters.serviceOrganisation.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-assignee",
    checkLabel: TEXT.tocFilters.assignee.title,
    selectValue: TEXT.tocFilters.assignee.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-airport",
    checkLabel: TEXT.tocFilters.airport.title,
    selectValue: TEXT.tocFilters.airport.value,
  });

  formMultiSelectDropdown({
    fieldName: "toc-filter-ifactor",
    checkLabel: TEXT.tocFilters.ifactor.title,
    selectIndex: [0],
  });

  formMultiSelectDropdown({
    fieldName: "toc-filter-unit-operational-status",
    checkLabel: TEXT.tocFilters.unitOperationalStatus.title,
    selectIndex: [0],
  });

  formMultiSelectDropdown({
    fieldName: "toc-filter-service-activity",
    checkLabel: TEXT.tocFilters.serviceActivity.title,
    selectIndex: [0],
  });

  formMultiSelectDropdown({
    fieldName: "toc-filter-status",
    checkLabel: TEXT.tocFilters.status.title,
    selectIndex: [0],
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-serial-number",
    checkLabel: TEXT.tocFilters.serialNumber.title,
    selectValue: TEXT.tocFilters.serialNumber.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-equipment-type",
    checkLabel: TEXT.tocFilters.equipmentType.title,
    selectValue: TEXT.tocFilters.equipmentType.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-models",
    checkLabel: TEXT.tocFilters.models.title,
    selectValue: TEXT.tocFilters.models.value,
  });

  formMultiSelectDropdown({
    fieldName: "toc-filter-payer",
    checkLabel: TEXT.tocFilters.payer.title,
    selectIndex: [0],
  });

  formMultiSelectDropdown({
    fieldName: "toc-filter-tags",
    checkLabel: TEXT.tocFilters.tags.title,
    selectIndex: [0],
  });

  formMultiSelectDropdown({
    fieldName: "toc-filter-sales-organisation",
    checkLabel: TEXT.tocFilters.salesOrganization.title,
    selectIndex: [0],
  });

  formMultiSelectDropdown({
    fieldName: "toc-filter-manufacturer-location",
    checkLabel: TEXT.tocFilters.manufacturerLocation.title,
    selectIndex: [0],
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-created-by",
    checkLabel: TEXT.tocFilters.createdBy.title,
    selectValue: TEXT.tocFilters.createdBy.value,
  });

  cy.getBySel("toc-filter-created-after-label").contains(
    TEXT.tocFilters.createdAfter.title
  );
  cy.getBySel("toc-filter-created-before-label").contains(
    TEXT.tocFilters.createdBefore.title
  );
  cy.getBySel("toc-filter-solved-after-label").contains(
    TEXT.tocFilters.solvedAfter.title
  );
  cy.getBySel("toc-filter-solved-before-label").contains(
    TEXT.tocFilters.solvedBefore.title
  );
  formSingleSelectDropdown({
    fieldName: "toc-filter-late",
    checkLabel: TEXT.tocFilters.late.title,
    selectIndex: 0,
  });
  formSingleSelectDropdown({
    fieldName: "toc-filter-factory-flag",
    checkLabel: TEXT.tocFilters.factoryFlag.title,
    selectIndex: 0,
  });
  formSingleSelectDropdown({
    fieldName: "toc-filter-factory-flag-recently-closed",
    checkLabel: TEXT.tocFilters.factoryFlagRecentlyClosed.title,
    selectIndex: 0,
  });
  formSingleSelectDropdown({
    fieldName: "toc-filter-survey",
    checkLabel: TEXT.tocFilters.survey.title,
    selectIndex: 0,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-buyer",
    checkLabel: TEXT.tocFilters.buyer.title,
    selectValue: TEXT.tocFilters.buyer.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-country",
    checkLabel: TEXT.tocFilters.country.title,
    selectValue: TEXT.tocFilters.country.value,
  });

  formField({
    fieldName: "toc-part",
    checkLabel: TEXT.tocFilters.tocPart.title,
    setValue: TEXT.tocFilters.tocPart.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-end-user",
    checkLabel: TEXT.tocFilters.endUser.title,
    selectValue: TEXT.tocFilters.endUser.value,
  });

  formField({
    fieldName: "spr-part",
    checkLabel: TEXT.tocFilters.sprPart.title,
    setValue: TEXT.tocFilters.sprPart.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-maintainer",
    checkLabel: TEXT.tocFilters.maintainer.title,
    selectValue: TEXT.tocFilters.maintainer.value,
  });

  formSingleSelectDropdown({
    fieldName: "toc-filter-confidential",
    checkLabel: TEXT.tocFilters.confidential.title,
    selectIndex: 0,
  });

  formField({
    fieldName: "toc-title",
    checkLabel: TEXT.tocFilters.title.title,
    setValue: TEXT.tocFilters.title.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-technician",
    checkLabel: TEXT.tocFilters.technician.title,
    selectValue: TEXT.tocFilters.technician.value,
  });

  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-assignee-or-technician",
    checkLabel: TEXT.tocFilters.assigneeOrTechnician.title,
    selectValue: TEXT.tocFilters.assigneeOrTechnician.value,
  });

  formField({
    fieldName: "toc-error-codes",
    checkLabel: TEXT.tocFilters.errorCodes.title,
    setValue: TEXT.tocFilters.errorCodes.value,
  });

  checkDateRange(
    "toc-filter-created-after",
    "toc-filter-created-before",
    TEXT.tocFilters.createdAfter.error
  );
  checkDateRange(
    "toc-filter-solved-after",
    "toc-filter-solved-before",
    TEXT.tocFilters.solvedAfter.error
  );

  cy.getBySel("toc-filter-submit").click();

  const params: { [key: string]: string | RegExp } = {
    equipmentRecord: /\/equipment_records\/\d+/,
    "status[]": REGEX.word,
    "assignee[]": REGEX.people,
    "unitOperationalStatus[]": "/unit_operational_statuses/MCF",
    "technicianOnCallType[]": /\/service\/technician_on_call_types\/\d+/,
    "serviceActivity[]": /\/service\/service_activities\/\d+/,
    "indiceFactor[]": "IF 1",
    "tags[]": /\/technician_on_call_tags\/\d+/,
    "createdBy[]": REGEX.people,
    "createdAt[after]": formatDate(dayjs().toISOString()),
    "createdAt[before]": formatDate(dayjs().toISOString()),
    "solvedAt[after]": formatDate(dayjs().toISOString()),
    "solvedAt[before]": formatDate(dayjs().toISOString()),
    "equipmentRecord.salesOrganisation[]": /\/locations\/\d+/,
    "salesOrganisationService[]": /\/locations\/\d+/,
    "equipmentRecord.manufacturerLocation[]": /\/locations\/\d+/,
    "equipmentRecord.product.family.productType[]":
      /\/sales\/product_types\/\d+/,
    "equipmentRecord.product[]": /\/sales\/products\/\d+/,
    "airport[]": /\/airports\/\d+/,
    late: REGEX.boolean,
    factoryFlag: REGEX.boolean,
    factoryFlagRecentlyClosed: REGEX.boolean,
    surveyAnswerThisMonth: REGEX.boolean,
    "equipmentRecord.buyer[]": /\/sales\/customers\/\d+/,
    "airport.country[]": /\/countries\/\d+/,
    "parts.partNumber": /\w+/,
    "equipmentRecord.endUser[]": /\/sales\/customers\/\d+/,
    "sparePartsRequests.parts.partNumber": /\w+/,
    confidential: REGEX.boolean,
    title: REGEX.sentence,
    "technician[]": REGEX.people,
    errorCodes: REGEX.sentence,
    "actor[]": REGEX.people,
  };
  cy.waitForApiWithLoader(API_CALL.getTocList, 200, params);

  cy.getBySel("toc-list-filter-icon").contains(TEXT.tocFilters.filterCount);
  cy.getBySel("toc-list-filter-icon").click();
  cy.url().should("include", TEXT.tocFilters.filterUrl);

  for (let i = 0; i < +TEXT.tocFilters.clearFilterCount; i++) {
    cy.get(
      "[data-testid^=CancelIcon], [data-testid^=ClearIcon], [data-testid^=CloseIcon]"
    )
      .first()
      .click({ force: true });
  }

  formField({
    fieldName: "toc-part",
    clear: true,
  });

  formField({
    fieldName: "spr-part",
    clear: true,
  });

  formField({
    fieldName: "toc-title",
    clear: true,
  });

  formField({
    fieldName: "toc-error-codes",
    clear: true,
  });

  cy.getBySel("toc-filter-submit").click();
  cy.waitForApiWithLoader(API_CALL.getTocList, 200);
  cy.getBySel("toc-card").should("have.length", 5);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.getTocList);
  cy.visit("/");
  cy.loginWithUser();
};

describe("TOC Filter Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkFilters();
  });
});
